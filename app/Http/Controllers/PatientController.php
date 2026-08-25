<?php

namespace App\Http\Controllers;

use App\Domain\Support\ProcStatus;
use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdPatientBalance;
use App\Models\OdProcedure;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\OdTreatmentPlanAttachments;
use App\Models\Office;
use App\Models\TreatmentPlan;
use App\Services\OpenDental\AccountModuleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{
    public function __construct(
        protected AccountModuleService $ar
    ) {}

    public function index()
    {
        $exportColumns = self::getExportableColumns();
        $clinics = Office::all();

        return view('patients.index', compact('exportColumns', 'clinics'));
    }

    public function data()
    {
        $query = OdPatient::query()
            ->select('od_patients.*')
            ->selectSub(function ($q) {
                // Fetch Guarantor Full Name via SubQuery Map securely
                $concatSql = DB::getDriverName() === 'sqlite'
                    ? "LName || ', ' || FName"
                    : 'CONCAT(LName, ", ", FName)';

                $q->from('od_patients as gp')
                    ->selectRaw($concatSql)
                    ->whereColumn('gp.PatNum', 'od_patients.Guarantor')
                    ->limit(1);
            }, 'guarantor_name')
            ->selectSub(function ($q) {
                $q->from('od_appointments')
                    ->selectRaw('MIN(AptDateTime)')
                    ->whereColumn('od_appointments.PatNum', 'od_patients.PatNum');
            }, 'first_visit')
            ->selectSub(function ($q) {
                $q->from('od_procedure_logs')
                    ->selectRaw('COALESCE(SUM(CAST(ProcFee AS DECIMAL(12,2))), 0)')
                    ->whereColumn('od_procedure_logs.PatNum', 'od_patients.PatNum');
            }, 'lifetime_production')
            ->selectSub(function ($q) {
                $q->from('od_pay_splits')
                    ->selectRaw('COALESCE(SUM(CAST(SplitAmt AS DECIMAL(12,2))), 0)')
                    ->whereColumn('od_pay_splits.PatNum', 'od_patients.PatNum');
            }, 'lifetime_collection');

        return DataTables::eloquent($query)
            ->addColumn('id', fn ($patient) => $patient->PatNum)
            ->addColumn('name', fn ($patient) => trim(($patient->LName ?? '').' '.($patient->FName ?? '')))
            ->addColumn('patient_id', fn ($patient) => $patient->PatNum)
            ->addColumn('guarantor', fn ($patient) => $patient->guarantor_name ?? '')
            ->addColumn('guarantor_id', fn ($patient) => $patient->Guarantor ?? '')
            ->addColumn('age', function ($patient) {
                $dobStr = $patient->Birthdate ?? null;
                if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
                    return (new \DateTime($dobStr))->diff(new \DateTime)->y;
                }

                return 'N/A';
            })
            ->addColumn('gender', function ($patient) {
                $genderMap = [0 => 'Male', 1 => 'Female', 2 => 'Unknown'];
                $raw = $patient->Gender ?? '';

                return is_numeric($raw) ? ($genderMap[intval($raw)] ?? 'Unknown') : ($raw ?: 'Unknown');
            })
            ->addColumn('address', fn ($patient) => trim(($patient->Address ?? '').' '.($patient->Address2 ?? '')))
            ->addColumn('city', fn ($patient) => $patient->City ?? '')
            ->addColumn('state', fn ($patient) => $patient->State ?? '')
            ->addColumn('zip', fn ($patient) => $patient->Zip ?? '')
            ->addColumn('work_phone', fn ($patient) => $patient->WkPhone ?? '')
            ->addColumn('home_phone', fn ($patient) => $patient->HmPhone ?? '')
            ->addColumn('mobile_phone', fn ($patient) => $patient->WirelessPhone ?? '')
            ->addColumn('email', fn ($patient) => $patient->Email ?? '')
            ->addColumn('zip', fn ($patient) => $patient->Zip ?? '')
            ->addColumn('work_phone', fn ($patient) => $patient->WkPhone ?? '')
            ->addColumn('home_phone', fn ($patient) => $patient->HmPhone ?? '')
            ->addColumn('mobile_phone', fn ($patient) => $patient->WirelessPhone ?? '')
            ->addColumn('email', fn ($patient) => $patient->Email ?? '')
            ->addColumn('birthdate', function ($patient) {
                $dobStr = $patient->Birthdate ?? null;
                if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
                    return (new \DateTime($dobStr))->format('M d, Y');
                }

                return 'N/A';
            })
            ->addColumn('first_visit', function ($patient) {
                return $patient->first_visit ? date('M d, Y', strtotime($patient->first_visit)) : 'N/A';
            })

            ->addColumn('lifetime_value_production', fn ($patient) => floatval($patient->lifetime_production))
            ->addColumn('lifetime_value_collection', fn ($patient) => floatval($patient->lifetime_collection))
            ->addColumn('referral_source', fn ($patient) => 'N/A')
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('LName', 'like', "%{$keyword}%")
                        ->orWhere('FName', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(LName, ' ', FName) like ?", ["%{$keyword}%"]);
                });
            })
            ->make(true);
    }

    public function show($id, Request $request)
    {
        $patient = OdPatient::where('PatNum', $id)->first();

        if (! $patient) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Patient not found'], 404);
            }
            abort(404);
        }

        if (! $request->expectsJson() && ! $request->ajax()) {
            return redirect()->route('patients.index', ['open_patient_id' => $id]);

        }

        $dobStr = $patient->Birthdate ?? null;
        $age = 'N/A';
        $birthdateFormatted = 'N/A';

        if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
            $dob = new \DateTime($dobStr);
            $age = $dob->diff(new \DateTime)->y;
            $birthdateFormatted = $dob->format('M d, Y');
        }

        $genderMap = [0 => 'Male', 1 => 'Female', 2 => 'Unknown'];
        $genderRaw = $patient->Gender ?? '';
        $gender = is_numeric($genderRaw) ? ($genderMap[intval($genderRaw)] ?? 'Unknown') : ($genderRaw ?: 'Unknown');

        $statusMap = [0 => 'Active', 1 => 'NonPatient', 2 => 'Inactive', 3 => 'Archived', 4 => 'Deceased', 5 => 'Prospective'];
        $statusRaw = $patient->PatStatus ?? '';
        $status = is_numeric($statusRaw) ? ($statusMap[intval($statusRaw)] ?? 'Active') : ($statusRaw ?: 'Active');

        $patientAppointments = OdAppointment::where('PatNum', $id)->get();
        $patientProcedures = OdProcedureLog::where('PatNum', $id)->get();
        $provMap = OdProvider::all()->pluck('LName', 'ProvNum')->toArray();

        $nowStr = now()->format('Y-m-d H:i:s');

        $nextApt = $patientAppointments
            ->filter(fn ($apt) => ($apt->AptDateTime ?? '') >= $nowStr)
            ->sortBy('AptDateTime')
            ->first();

        $lastApt = $patientAppointments
            ->filter(fn ($apt) => ($apt->AptDateTime ?? '') < $nowStr)
            ->sortByDesc('AptDateTime')
            ->first();

        $completedCount = $patientAppointments->filter(fn ($apt) => in_array($apt->AptStatus ?? '', [2, 'Complete', 'Completed']))->count();
        $scheduledCount = $patientAppointments->filter(fn ($apt) => in_array($apt->AptStatus ?? '', [1, 'Scheduled', 'Active', 'Scheduled/Active']))->count();
        $brokenCount = $patientAppointments->filter(fn ($apt) => in_array($apt->AptStatus ?? '', [5, 'Broken']))->count();
        $totalApts = $completedCount + $scheduledCount + $brokenCount;

        $completedPct = $totalApts > 0 ? round(($completedCount / $totalApts) * 100, 2) : 0.00;
        $scheduledPct = $totalApts > 0 ? round(($scheduledCount / $totalApts) * 100, 2) : 0.00;
        $brokenPct = $totalApts > 0 ? round(($brokenCount / $totalApts) * 100, 2) : 0.00;

        $lifetimeValue = floatval($patientProcedures->sum('ProcFee'));
        $tpProcedures = $patientProcedures->filter(fn ($p) => in_array($p->ProcStatus ?? '', ProcStatus::TREATMENT_PLANNED));
        $scheduledTP = $tpProcedures->filter(fn ($p) => ($p->AptNum ?? 0) > 0);
        $unscheduledTP = $tpProcedures->filter(fn ($p) => ($p->AptNum ?? 0) == 0);
        $scheduledTPFee = floatval($scheduledTP->sum('ProcFee'));
        $unscheduledTPFee = floatval($unscheduledTP->sum('ProcFee'));

        $codeMap = OdProcedure::all()->keyBy('CodeNum');

        $ledgerItems = [];
        foreach ($patientProcedures->whereIn('ProcStatus', ProcStatus::completed()) as $proc) {
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $codeRecord = isset($proc->CodeNum) ? $codeMap->get($proc->CodeNum) : null;
            $codeStr = $codeRecord->ProcCode ?? ($proc->OldCode ?? ($proc->ProcCode ?? '—'));
            $descStr = $codeRecord->Descript ?? ($proc->Descript ?? 'Procedure');

            $ledgerItems[] = [
                'code' => $codeStr,
                'description' => $descStr,
                'tooth' => $proc->ToothNum ?? '',
                'surface' => $proc->Surf ?? '',
                'amount' => '$ '.number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'provider_id' => $provNum,
                'date' => isset($proc->ProcDate) ? date('M d, Y', strtotime($proc->ProcDate)) : '—',
                'timestamp' => strtotime($proc->ProcDate ?? ''),
            ];
        }

        usort($ledgerItems, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        $provMap = OdProvider::all()->mapWithKeys(function ($p) {
            $name = trim(($p->LName ?? '').($p->FName ? ', '.$p->FName : ''));

            return [$p->ProvNum => $name ?: ($p->Abbr ?? '—')];
        })->toArray();

        $codeMap = OdProcedure::all()->keyBy('CodeNum');

        $txplansItems = $this->getPatientTxPlans($id, $patientProcedures, $patientAppointments, $provMap, $codeMap);

        $notes = $patient->AddrNote ?? 'No activities or notes available.';

        return response()->json([
            'id' => $patient->PatNum,
            'name' => ($patient->LName ?? '').', '.($patient->FName ?? ''),
            'age' => $age,
            'gender' => $gender,
            'birthdate' => $birthdateFormatted,
            'status' => $status,
            'mobile_phone' => $patient->WirelessPhone ?: 'N/A',
            'work_phone' => $patient->WkPhone ?: 'N/A',
            'home_phone' => $patient->HmPhone ?: 'N/A',
            'email' => $patient->Email ?: 'N/A',
            'address' => trim(($patient->Address ?? '').' '.($patient->Address2 ?? '')),
            'city' => $patient->City ?? '',
            'state' => $patient->State ?? '',
            'zip' => $patient->Zip ?? '',
            'overview' => [
                'next_visit' => [
                    'date' => $nextApt ? date('M d, Y', strtotime($nextApt->AptDateTime)) : '-',
                    'fee' => 0.00,
                    'label' => $nextApt->ProcDescript ?? 'N/A',
                ],
                'last_visit' => [
                    'date' => $lastApt ? date('M d, Y', strtotime($lastApt->AptDateTime)) : '-',
                    'fee' => 0.00,
                    'label' => $lastApt->ProcDescript ?? 'N/A',
                ],
                'remaining_insurance' => 0.00,
                'treatment_plans' => [
                    'scheduled' => $scheduledTPFee,
                    'unscheduled' => $unscheduledTPFee,
                ],
                'hygiene_due' => $patient->DateRecallDue ?? '—',
                'lifetime_production' => $lifetimeValue,
                'appointments' => [
                    'completed' => [
                        'count' => $completedCount,
                        'percent' => number_format($completedPct, 2),
                    ],
                    'scheduled' => [
                        'count' => $scheduledCount,
                        'percent' => number_format($scheduledPct, 2),
                    ],
                    'broken' => [
                        'count' => $brokenCount,
                        'percent' => number_format($brokenPct, 2),
                    ],
                ],
            ],
            'ledger' => $ledgerItems,
            'txplans' => $txplansItems,
            'notes' => $notes,
        ]);
    }

    public function showTreatment($patientId)
    {
        $items = $this->getPatientTxPlans($patientId);

        return response()->json($items);
    }

    private function getPatientTxPlans($patientId, $procedures = null, $appointments = null, $provMap = null, $codeMap = null): array
    {
        $planNums = TreatmentPlan::where('PatNum', $patientId)->pluck('TreatPlanNum');
        $attachedProcNums = OdTreatmentPlanAttachments::whereIn('TreatPlanNum', $planNums)
            ->pluck('ProcNum')
            ->filter()
            ->unique();

        if ($procedures === null) {
            $procedures = OdProcedureLog::where('PatNum', $patientId)
                ->where(function ($query) use ($attachedProcNums) {
                    $query->whereIn('ProcStatus', ProcStatus::TREATMENT_PLANNED)
                        ->orWhere(function ($q) {
                            $q->whereNotNull('DateTP')
                                ->where('DateTP', '!=', '0001-01-01');
                        });
                    if ($attachedProcNums->isNotEmpty()) {
                        $query->orWhereIn('ProcNum', $attachedProcNums);
                    }
                })
                ->get();
        } else {
            $procedures = $procedures->filter(function ($proc) use ($attachedProcNums) {
                return in_array($proc->ProcStatus ?? '', ProcStatus::TREATMENT_PLANNED)
                    || (! empty($proc->DateTP) && $proc->DateTP !== '0001-01-01')
                    || ($attachedProcNums->isNotEmpty() && $attachedProcNums->contains($proc->ProcNum));
            });
        }

        if ($provMap === null) {
            $provMap = OdProvider::all()->mapWithKeys(function ($p) {
                $name = trim(($p->LName ?? '').($p->FName ? ', '.$p->FName : ''));

                return [$p->ProvNum => $name ?: ($p->Abbr ?? '—')];
            })->toArray();
        }

        if ($codeMap === null) {
            $codeMap = OdProcedure::all()->keyBy('CodeNum');
        }

        if ($appointments === null) {
            $aptNums = $procedures->pluck('AptNum')->filter(fn ($aptNum) => ($aptNum ?? 0) > 0)->unique();
            $appointments = OdAppointment::whereIn('AptNum', $aptNums)->get()->keyBy('AptNum');
        }

        $firstTp = TreatmentPlan::where('PatNum', $patientId)->orderBy('DateTP')->first();
        $masterTpDate = ($firstTp && ! empty($firstTp->DateTP) && $firstTp->DateTP !== '0001-01-01')
            ? date('M d, Y', strtotime($firstTp->DateTP))
            : null;

        $items = $procedures->map(function ($proc) use ($provMap, $codeMap, $appointments, $masterTpDate) {
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $codeRecord = isset($proc->CodeNum) ? $codeMap->get($proc->CodeNum) : null;
            $code = $codeRecord->ProcCode ?? ($proc->OldCode ?? ($proc->ProcCode ?? '—'));
            $description = $codeRecord->Descript ?? ($proc->Descript ?? 'Procedure');

            $rawStatus = $proc->ProcStatus ?? '';
            $apt = ($proc->AptNum ?? 0) > 0 ? $appointments->get($proc->AptNum) : null;
            $statusText = 'Unscheduled';

            if ($rawStatus === 'C' || $rawStatus === '2') {
                $statusText = 'Completed';
            } elseif ($rawStatus === 'D' || $rawStatus === '6') {
                $statusText = 'Deleted';
            } elseif ($apt) {
                $aptStatus = (string) ($apt->AptStatus ?? '');
                if ($aptStatus === '5' || strtolower($aptStatus) === 'broken') {
                    $statusText = 'Broken';
                } elseif ($aptStatus === '2' || strtolower($aptStatus) === 'complete' || strtolower($aptStatus) === 'completed') {
                    $statusText = 'Completed';
                } else {
                    $statusText = 'Scheduled';
                }
            } elseif (in_array($rawStatus, ProcStatus::TREATMENT_PLANNED)) {
                $statusText = 'Unscheduled';
            }

            $datePlanned = isset($proc->DateTP) && $proc->DateTP !== '0001-01-01'
                ? date('M d, Y', strtotime($proc->DateTP))
                : '—';

            $dateScheduled = '—';
            if (($proc->AptNum ?? 0) > 0 && $apt && ! empty($apt->AptDateTime) && $apt->AptDateTime !== '0001-01-01 00:00:00') {
                $dateScheduled = date('M d, Y', strtotime($apt->AptDateTime));
            }

            $dateCompleted = ($rawStatus === 'C' || $rawStatus === '2') && isset($proc->ProcDate) && $proc->ProcDate !== '0001-01-01'
                ? date('M d, Y', strtotime($proc->ProcDate))
                : '—';

            if ($statusText === 'Completed') {
                $dateCreated = '—';
            } elseif ($masterTpDate) {
                $dateCreated = $masterTpDate;
            } elseif (isset($proc->SecDateEntry) && $proc->SecDateEntry !== '0001-01-01') {
                $dateCreated = date('M d, Y', strtotime($proc->SecDateEntry));
            } elseif (isset($proc->DateEntryC) && $proc->DateEntryC !== '0001-01-01') {
                $dateCreated = date('M d, Y', strtotime($proc->DateEntryC));
            } elseif (isset($proc->DateTP) && $proc->DateTP !== '0001-01-01') {
                $dateCreated = date('M d, Y', strtotime($proc->DateTP));
            } else {
                $dateCreated = '—';
            }

            $sortTimestamp = 0;
            if ($statusText === 'Completed' && ! empty($proc->ProcDate) && $proc->ProcDate !== '0001-01-01') {
                $sortTimestamp = strtotime($proc->ProcDate);
            } elseif ($apt && ! empty($apt->AptDateTime) && $apt->AptDateTime !== '0001-01-01 00:00:00') {
                $sortTimestamp = strtotime($apt->AptDateTime);
            } elseif (! empty($proc->DateTP) && $proc->DateTP !== '0001-01-01') {
                $sortTimestamp = strtotime($proc->DateTP);
            }

            return [
                'code' => $code,
                'description' => $description,
                'tooth' => $proc->ToothNum ?? '',
                'surface' => $proc->Surf ?? '',
                'amount' => '$ '.number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'provider_id' => $provNum,
                'status' => $statusText,
                'planned' => $datePlanned,
                'scheduled' => $dateScheduled,
                'completed' => $dateCompleted,
                'date_planned' => $datePlanned,
                'date_scheduled' => $dateScheduled,
                'date_completed' => $dateCompleted,
                'date_created' => $dateCreated,
                'is_active' => $statusText === 'Unscheduled' ? 0 : 1,
                'timestamp' => $sortTimestamp,
            ];
        })
            ->sort(function ($a, $b) {
                if ($a['is_active'] !== $b['is_active']) {
                    return $b['is_active'] <=> $a['is_active'];
                }

                return $b['timestamp'] <=> $a['timestamp'];
            })
            ->values()
            ->map(fn ($item) => collect($item)->except(['timestamp', 'is_active'])->all())
            ->all();

        return $items;
    }

    public function showFamily($patientId)
    {
        $patient = OdPatient::where('PatNum', $patientId)->first();

        if (! $patient) {
            return response()->json([], 404);
        }

        $guarantorId = $patient->Guarantor ?? null;

        if (! $guarantorId) {
            return response()->json([]);
        }

        $genderMap = [0 => 'Male', 1 => 'Female', 2 => 'Unknown'];
        $statusMap = [0 => 'Active', 1 => 'NonPatient', 2 => 'Inactive', 3 => 'Archived', 4 => 'Deceased', 5 => 'Prospective'];
        $nowStr = now()->format('Y-m-d H:i:s');

        $familyMembers = OdPatient::where('Guarantor', $guarantorId)->get();

        return response()->json($familyMembers->map(function ($m) use ($genderMap, $statusMap, $nowStr) {
            $mApts = OdAppointment::where('PatNum', $m->PatNum)->get();
            $mNext = $mApts->filter(fn ($apt) => ($apt->AptDateTime ?? '') >= $nowStr)->sortBy('AptDateTime')->first();
            $mLast = $mApts->filter(fn ($apt) => ($apt->AptDateTime ?? '') < $nowStr)->sortByDesc('AptDateTime')->first();

            $mGenderRaw = $m->Gender ?? '';
            $mGender = is_numeric($mGenderRaw) ? ($genderMap[intval($mGenderRaw)] ?? 'Unknown') : ($mGenderRaw ?: 'Unknown');
            $mStatusRaw = $m->PatStatus ?? '';
            $mStatus = is_numeric($mStatusRaw) ? ($statusMap[intval($mStatusRaw)] ?? 'Active') : ($mStatusRaw ?: 'Active');

            return [
                'name' => ($m->LName ?? '').', '.($m->FName ?? ''),
                'status' => $mStatus,
                'gender' => $mGender,
                'last_visit' => $mLast ? date('M d, Y', strtotime($mLast->AptDateTime)) : '—',
                'next_visit' => $mNext ? date('M d, Y', strtotime($mNext->AptDateTime)) : '—',
                'hygiene_due' => $m->DateRecallDue ?? '—',
            ];
        })->values());
    }

    public function showEmployer($patientId)
    {
        $patient = OdPatient::where('PatNum', $patientId)->first();

        if (! $patient) {
            return response()->json(['name' => null], 404);
        }

        $employerNum = $patient->EmployerNum ?? null;
        $employmentNote = $patient->EmploymentNote ?? null;

        return response()->json([
            'employer_num' => $employerNum,
            'name' => $employerNum ? 'Employer #'.$employerNum : null,
            'note' => $employmentNote,
        ]);
    }

    public function showAR($patientId)
    {
        $ar = OdPatientBalance::where('PatNum', $patientId)->first();

        if (! $ar) {
            return response()->json([
                'total' => 0,
                'insurance_claims' => 0,
                'estimated_patient' => 0,
                'aging' => [
                    'current' => 0,
                    '30_days' => 0,
                    '60_days' => 0,
                    '90_days' => 0,
                ],
            ], 201);
        }

        return response()->json([
            'total' => number_format($ar->Total ?? 0, 2),
            'insurance_claims' => number_format($ar->InsEst ?? 0, 2),
            'estimated_patient' => number_format($ar->EstBal ?? 0, 2),
            'aging' => [
                'current' => number_format($ar->Bal_0_30 ?? 0, 2),
                '30_days' => number_format($ar->Bal_31_60 ?? 0, 2),
                '60_days' => number_format($ar->Bal_61_90 ?? 0, 2),
                '90_days' => number_format($ar->BalOver90 ?? 0, 2),
            ],
        ]);
    }

    public function showArLive($patientId)
    {
        // Fetch Live Aging Array from Service
        $ar = $this->ar->aging($patientId);

        // Fetch Live completed transactions for the sub-log
        $patientProcedures = OdProcedureLog::where('PatNum', $patientId)->whereIn('ProcStatus', ProcStatus::completed())->get();
        $provMap = OdProvider::all()->pluck('LName', 'ProvNum')->toArray();
        $codeMap = OdProcedure::all()->keyBy('CodeNum');

        $arTransactions = [];
        foreach ($patientProcedures as $proc) {
            if (floatval($proc->ProcFee ?? 0) <= 0) {
                continue;
            }
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $codeRecord = isset($proc->CodeNum) ? $codeMap->get($proc->CodeNum) : null;
            $codeStr = $codeRecord->ProcCode ?? ($proc->OldCode ?? ($proc->ProcCode ?? '—'));
            $descStr = $codeRecord->Descript ?? ($proc->Descript ?? 'Procedure');

            $arTransactions[] = [
                'description' => $descStr,
                'code' => $codeStr,
                'amount' => '$ '.number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'provider_id' => $provNum,
                'date' => isset($proc->ProcDate) ? date('M d, Y', strtotime($proc->ProcDate)) : '—',
            ];
        }

        return response()->json([
            'total' => number_format($ar['Total'] ?? 0, 2),
            'insurance_claims' => number_format($ar['InsEst'] ?? 0, 2),
            'estimated_patient' => number_format($ar['EstBal'] ?? 0, 2),
            'current' => number_format($ar['Bal_0_30'] ?? 0, 2),
            'thirty_days' => number_format($ar['Bal_31_60'] ?? 0, 2),
            'sixty_days' => number_format($ar['Bal_61_90'] ?? 0, 2),
            'ninety_days' => number_format($ar['BalOver90'] ?? 0, 2),
            'transactions' => $arTransactions,
        ]);
    }

    public static function getExportableColumns(): array
    {
        return [
            // Identification
            'patient_id' => ['label' => 'Patient ID', 'category' => 'Identification', 'default' => true],
            'first_name' => ['label' => 'First Name', 'category' => 'Identification', 'default' => true],
            'last_name' => ['label' => 'Last Name', 'category' => 'Identification', 'default' => true],
            'full_name' => ['label' => 'Full Name', 'category' => 'Identification', 'default' => true],
            'birthdate' => ['label' => 'Birthdate', 'category' => 'Identification', 'default' => false],
            'age' => ['label' => 'Age', 'category' => 'Identification', 'default' => false],
            'gender' => ['label' => 'Gender', 'category' => 'Identification', 'default' => false],
            'ssn' => ['label' => 'SSN', 'category' => 'Identification', 'default' => false],
            'chart_number' => ['label' => 'Chart Number', 'category' => 'Identification', 'default' => false],
            'status' => ['label' => 'Status', 'category' => 'Identification', 'default' => false],
            'date_added' => ['label' => 'Date Added (Open Dental)', 'category' => 'Identification', 'default' => true],

            // Contact Info
            'email' => ['label' => 'Email Address', 'category' => 'Contact Info', 'default' => true],
            'mobile_phone' => ['label' => 'Mobile Phone', 'category' => 'Contact Info', 'default' => true],
            'home_phone' => ['label' => 'Home Phone', 'category' => 'Contact Info', 'default' => false],
            'work_phone' => ['label' => 'Work Phone', 'category' => 'Contact Info', 'default' => false],
            'address' => ['label' => 'Street Address', 'category' => 'Contact Info', 'default' => true],
            'address2' => ['label' => 'Address Line 2', 'category' => 'Contact Info', 'default' => false],
            'city' => ['label' => 'City', 'category' => 'Contact Info', 'default' => true],
            'state' => ['label' => 'State', 'category' => 'Contact Info', 'default' => true],
            'zip' => ['label' => 'ZIP Code', 'category' => 'Contact Info', 'default' => true],

            // Financial & Account
            'guarantor_id' => ['label' => 'Guarantor ID', 'category' => 'Financial & Account', 'default' => false],
            'guarantor_name' => ['label' => 'Guarantor Name', 'category' => 'Financial & Account', 'default' => false],
            'bal_total' => ['label' => 'Total Balance', 'category' => 'Financial & Account', 'default' => false],
            'bal_0_30' => ['label' => 'Balance 0-30', 'category' => 'Financial & Account', 'default' => false],
            'bal_31_60' => ['label' => 'Balance 31-60', 'category' => 'Financial & Account', 'default' => false],
            'bal_61_90' => ['label' => 'Balance 61-90', 'category' => 'Financial & Account', 'default' => false],
            'bal_over_90' => ['label' => 'Balance Over 90', 'category' => 'Financial & Account', 'default' => false],
            'est_balance' => ['label' => 'Estimated Balance', 'category' => 'Financial & Account', 'default' => false],
            'ins_est' => ['label' => 'Insurance Estimate', 'category' => 'Financial & Account', 'default' => false],
            'billing_type' => ['label' => 'Billing Type', 'category' => 'Financial & Account', 'default' => false],
            'primary_provider' => ['label' => 'Primary Provider', 'category' => 'Financial & Account', 'default' => false],
        ];
    }

    protected function buildExportQuery(Request $request)
    {
        $query = OdPatient::query();

        // Location / Clinic filter
        if ($request->filled('clinic_id')) {
            $clinicId = $request->get('clinic_id');
            if ($clinicId !== 'all' && is_numeric($clinicId)) {
                $query->where('office_id', $clinicId);
            }
        }

        // Status filter
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('PatStatus', $request->get('status'));
        }

        // Keyword search filter
        if ($request->filled('search')) {
            $kw = trim($request->get('search'));
            if ($kw !== '') {
                $concatSql1 = DB::getDriverName() === 'sqlite' ? "(FName || ' ' || LName)" : "CONCAT(FName, ' ', LName)";
                $concatSql2 = DB::getDriverName() === 'sqlite' ? "(LName || ', ' || FName)" : "CONCAT(LName, ', ', FName)";

                $query->where(function ($q) use ($kw, $concatSql1, $concatSql2) {
                    $q->where('PatNum', 'like', "%{$kw}%")
                        ->orWhere('FName', 'like', "%{$kw}%")
                        ->orWhere('LName', 'like', "%{$kw}%")
                        ->orWhere('Email', 'like', "%{$kw}%")
                        ->orWhere('WirelessPhone', 'like', "%{$kw}%")
                        ->orWhere('HmPhone', 'like', "%{$kw}%")
                        ->orWhere('WkPhone', 'like', "%{$kw}%")
                        ->orWhere('City', 'like', "%{$kw}%")
                        ->orWhere('Zip', 'like', "%{$kw}%")
                        ->orWhereRaw("{$concatSql1} like ?", ["%{$kw}%"])
                        ->orWhereRaw("{$concatSql2} like ?", ["%{$kw}%"]);
                });
            }
        }

        // Date Added to Open Dental Filter
        $dateMode = $request->get('date_mode', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($dateMode !== 'all' && $dateMode !== 'custom') {
            $now = Carbon::now();
            if ($dateMode === 'today') {
                $dateFrom = $now->format('Y-m-d');
                $dateTo = $now->format('Y-m-d');
            } elseif ($dateMode === 'yesterday') {
                $dateFrom = $now->copy()->subDay()->format('Y-m-d');
                $dateTo = $now->copy()->subDay()->format('Y-m-d');
            } elseif ($dateMode === 'this_month') {
                $dateFrom = $now->copy()->startOfMonth()->format('Y-m-d');
                $dateTo = $now->copy()->endOfMonth()->format('Y-m-d');
            } elseif ($dateMode === 'last_month') {
                $dateFrom = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $dateTo = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
            } elseif ($dateMode === 'this_year') {
                $dateFrom = $now->copy()->startOfYear()->format('Y-m-d');
                $dateTo = $now->copy()->endOfYear()->format('Y-m-d');
            } elseif ($dateMode === 'last_7_days') {
                $dateFrom = $now->copy()->subDays(7)->format('Y-m-d');
                $dateTo = $now->format('Y-m-d');
            } elseif ($dateMode === 'last_30_days') {
                $dateFrom = $now->copy()->subDays(30)->format('Y-m-d');
                $dateTo = $now->format('Y-m-d');
            }
        }

        if (! empty($dateFrom)) {
            $query->whereRaw("COALESCE(NULLIF(od_patients.SecDateEntry, '0001-01-01'), NULLIF(od_patients.DateFirstVisit, '0001-01-01'), DATE(od_patients.created_at)) >= ?", [$dateFrom]);
        }
        if (! empty($dateTo)) {
            $query->whereRaw("COALESCE(NULLIF(od_patients.SecDateEntry, '0001-01-01'), NULLIF(od_patients.DateFirstVisit, '0001-01-01'), DATE(od_patients.created_at)) <= ?", [$dateTo]);
        }

        return $query;
    }

    public function exportData(Request $request)
    {
        $query = $this->buildExportQuery($request);
        $total = (clone $query)->count();

        // Selected Columns
        $selectedCols = $request->get('columns', []);
        if (is_string($selectedCols)) {
            $selectedCols = explode(',', $selectedCols);
        }
        $selectedCols = array_filter((array) $selectedCols);
        if (empty($selectedCols)) {
            $allCols = self::getExportableColumns();
            $selectedCols = array_keys(array_filter($allCols, fn ($c) => $c['default']));
        }

        $page = max(1, intval($request->get('page', 1)));
        $perPage = max(10, min(100, intval($request->get('per_page', 20))));

        $patients = $query->orderBy('PatNum', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Preload Guarantors and Providers for efficient mapping
        $guarantorIds = $patients->pluck('Guarantor')->filter()->unique()->toArray();
        $guarantorMap = [];
        if (! empty($guarantorIds)) {
            $guarantorMap = OdPatient::whereIn('PatNum', $guarantorIds)
                ->get()
                ->mapWithKeys(fn ($g) => [$g->PatNum => trim(($g->LName ?? '').', '.($g->FName ?? ''))])
                ->toArray();
        }

        $providerMap = OdProvider::all()->mapWithKeys(function ($p) {
            $name = trim(($p->LName ?? '').($p->FName ? ', '.$p->FName : ''));

            return [$p->ProvNum => $name ?: ($p->Abbr ?? '—')];
        })->toArray();

        $rows = [];
        foreach ($patients as $p) {
            $rows[] = $this->formatPatientExportRow($p, $selectedCols, $guarantorMap, $providerMap);
        }

        return response()->json([
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'selected_columns' => $selectedCols,
            'data' => $rows,
        ]);
    }

    public function exportDownload(Request $request)
    {
        $query = $this->buildExportQuery($request);
        $total = (clone $query)->count();

        // Selected Columns
        $selectedCols = $request->get('columns', []);
        if (is_string($selectedCols)) {
            $selectedCols = explode(',', $selectedCols);
        }
        $selectedCols = array_filter((array) $selectedCols);
        $allColsConfig = self::getExportableColumns();

        if (empty($selectedCols)) {
            $selectedCols = array_keys(array_filter($allColsConfig, fn ($c) => $c['default']));
        }

        $customName = $request->get('filename', 'patients_export');
        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customName) ?: 'patients_export';
        $filename = $cleanFilename.'_'.date('Y-m-d_His').'.csv';

        $providerMap = OdProvider::all()->mapWithKeys(function ($p) {
            $name = trim(($p->LName ?? '').($p->FName ? ', '.$p->FName : ''));

            return [$p->ProvNum => $name ?: ($p->Abbr ?? '—')];
        })->toArray();

        return response()->streamDownload(function () use ($query, $selectedCols, $allColsConfig, $providerMap) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Row
            $headerRow = [];
            foreach ($selectedCols as $colKey) {
                $headerRow[] = $allColsConfig[$colKey]['label'] ?? ucfirst(str_replace('_', ' ', $colKey));
            }
            fputcsv($handle, $headerRow);

            // Stream chunks of 500 rows
            $query->orderBy('PatNum', 'asc')->chunk(500, function ($patients) use ($handle, $selectedCols, $providerMap) {
                $guarantorIds = $patients->pluck('Guarantor')->filter()->unique()->toArray();
                $guarantorMap = [];
                if (! empty($guarantorIds)) {
                    $guarantorMap = OdPatient::whereIn('PatNum', $guarantorIds)
                        ->get()
                        ->mapWithKeys(fn ($g) => [$g->PatNum => trim(($g->LName ?? '').', '.($g->FName ?? ''))])
                        ->toArray();
                }

                foreach ($patients as $patient) {
                    $formatted = $this->formatPatientExportRow($patient, $selectedCols, $guarantorMap, $providerMap);
                    fputcsv($handle, array_values($formatted));
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    protected function formatPatientExportRow($patient, array $selectedCols, array $guarantorMap, array $providerMap): array
    {
        $dobStr = $patient->Birthdate ?? null;
        $age = 'N/A';
        $birthdateFormatted = '';
        if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
            $dob = new \DateTime($dobStr);
            $age = $dob->diff(new \DateTime)->y;
            $birthdateFormatted = $dob->format('Y-m-d');
        }

        $genderMap = [0 => 'Male', 1 => 'Female', 2 => 'Unknown'];
        $genderRaw = $patient->Gender ?? '';
        $gender = is_numeric($genderRaw) ? ($genderMap[intval($genderRaw)] ?? 'Unknown') : ($genderRaw ?: 'Unknown');

        $statusMap = [0 => 'Active', 1 => 'NonPatient', 2 => 'Inactive', 3 => 'Archived', 4 => 'Deceased', 5 => 'Prospective'];
        $statusRaw = $patient->PatStatus ?? '';
        $status = is_numeric($statusRaw) ? ($statusMap[intval($statusRaw)] ?? 'Active') : ($statusRaw ?: 'Active');

        $dateAddedFormatted = '';
        if (! empty($patient->SecDateEntry) && $patient->SecDateEntry !== '0001-01-01') {
            $dateAddedFormatted = date('Y-m-d', strtotime($patient->SecDateEntry));
        } elseif (! empty($patient->DateFirstVisit) && $patient->DateFirstVisit !== '0001-01-01') {
            $dateAddedFormatted = date('Y-m-d', strtotime($patient->DateFirstVisit));
        } elseif (! empty($patient->created_at)) {
            $dateAddedFormatted = is_string($patient->created_at)
                ? date('Y-m-d', strtotime($patient->created_at))
                : $patient->created_at->format('Y-m-d');
        }

        $allValues = [
            'patient_id' => $patient->PatNum,
            'first_name' => $patient->FName ?? '',
            'last_name' => $patient->LName ?? '',
            'full_name' => trim(($patient->LName ?? '').', '.($patient->FName ?? '')),
            'birthdate' => $birthdateFormatted,
            'age' => $age,
            'gender' => $gender,
            'ssn' => $patient->SSN ?? '',
            'chart_number' => $patient->ChartNumber ?? '',
            'status' => $status,
            'date_added' => $dateAddedFormatted,
            'email' => $patient->Email ?? '',
            'mobile_phone' => $patient->WirelessPhone ?? '',
            'home_phone' => $patient->HmPhone ?? '',
            'work_phone' => $patient->WkPhone ?? '',
            'address' => $patient->Address ?? '',
            'address2' => $patient->Address2 ?? '',
            'city' => $patient->City ?? '',
            'state' => $patient->State ?? '',
            'zip' => $patient->Zip ?? '',
            'guarantor_id' => $patient->Guarantor ?? '',
            'guarantor_name' => $guarantorMap[$patient->Guarantor] ?? '',
            'bal_total' => floatval($patient->BalTotal ?? 0),
            'bal_0_30' => floatval($patient->Bal_0_30 ?? 0),
            'bal_31_60' => floatval($patient->Bal_31_60 ?? 0),
            'bal_61_90' => floatval($patient->Bal_61_90 ?? 0),
            'bal_over_90' => floatval($patient->BalOver90 ?? 0),
            'est_balance' => floatval($patient->EstBalance ?? 0),
            'ins_est' => floatval($patient->InsEst ?? 0),
            'billing_type' => $patient->BillingType ?? '',
            'primary_provider' => $providerMap[$patient->PriProv] ?? ($patient->PriProv ?? ''),
        ];

        $result = [];
        foreach ($selectedCols as $col) {
            $result[$col] = $allValues[$col] ?? '';
        }

        return $result;
    }
}
