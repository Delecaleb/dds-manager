<?php

namespace App\Http\Controllers;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdPatientBalance;
use App\Models\OdProcedure;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\OdTreatmentPlanAttachments;
use App\Models\TreatmentPlan;
use App\Services\OpenDental\AccountModuleService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{
    public function __construct(
        protected AccountModuleService $ar
    ) {
    }

    public function index()
    {
        return view('patients.index');
    }

    public function data()
    {
        $query = OdPatient::query()
            ->select('od_patients.*')
            ->selectSub(function ($q) {
                $q->from('od_appointments')
                    ->selectRaw('MIN(AptDateTime)')
                    ->whereColumn('od_appointments.PatNum', 'od_patients.PatNum');
            }, 'first_visit')
            ->selectSub(function ($q) {
                $q->from('od_appointments')
                    ->selectRaw('MAX(AptDateTime)')
                    ->whereColumn('od_appointments.PatNum', 'od_patients.PatNum');
            }, 'last_visit')
            ->selectSub(function ($q) {
                $q->from('od_procedure_logs')
                    ->selectRaw('COALESCE(SUM(CAST(ProcFee AS DECIMAL(12,2))), 0)')
                    ->whereColumn('od_procedure_logs.PatNum', 'od_patients.PatNum');
            }, 'lifetime_production');

        return DataTables::eloquent($query)
            ->addColumn('id', fn($patient) => $patient->PatNum)
            ->addColumn('patient_id', fn($patient) => $patient->PatNum)
            ->addColumn('name', fn($patient) => trim(($patient->LName ?? '') . ' ' . ($patient->FName ?? '')))
            ->addColumn('phone', fn($patient) => $patient->WirelessPhone ?? '')
            ->addColumn('email', fn($patient) => $patient->Email ?? '')
            ->addColumn('birthdate', fn($patient) => $patient->Birthdate ?? '')
            ->addColumn('city', fn($patient) => $patient->City ?? '')
            ->addColumn('state', fn($patient) => $patient->State ?? '')
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('LName', 'like', "%{$keyword}%")
                        ->orWhere('FName', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(LName, ' ', FName) like ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('first_visit', fn($query, $keyword) => null)
            ->filterColumn('last_visit', fn($query, $keyword) => null)
            ->filterColumn('lifetime_production', fn($query, $keyword) => null)
            ->orderColumn('name', fn($query, $order) => $query->orderBy('LName', $order)->orderBy('FName', $order))
            ->orderColumn('first_visit', fn($query, $order) => $query->orderBy('first_visit', $order))
            ->orderColumn('last_visit', fn($query, $order) => $query->orderBy('last_visit', $order))
            ->orderColumn('lifetime_production', fn($query, $order) => $query->orderBy('lifetime_production', $order))
            ->make(true);
    }

    public function show($id)
    {
        $patient = OdPatient::where('PatNum', $id)->first();

        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $dobStr = $patient->Birthdate ?? null;
        $age = 'N/A';
        $birthdateFormatted = 'N/A';

        if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
            $dob = new \DateTime($dobStr);
            $age = $dob->diff(new \DateTime())->y;
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
            ->filter(fn($apt) => ($apt->AptDateTime ?? '') >= $nowStr)
            ->sortBy('AptDateTime')
            ->first();

        $lastApt = $patientAppointments
            ->filter(fn($apt) => ($apt->AptDateTime ?? '') < $nowStr)
            ->sortByDesc('AptDateTime')
            ->first();

        $completedCount = $patientAppointments->filter(fn($apt) => in_array($apt->AptStatus ?? '', [2, 'Complete', 'Completed']))->count();
        $scheduledCount = $patientAppointments->filter(fn($apt) => in_array($apt->AptStatus ?? '', [1, 'Scheduled', 'Active', 'Scheduled/Active']))->count();
        $brokenCount = $patientAppointments->filter(fn($apt) => in_array($apt->AptStatus ?? '', [5, 'Broken']))->count();
        $totalApts = $completedCount + $scheduledCount + $brokenCount;

        $completedPct = $totalApts > 0 ? round(($completedCount / $totalApts) * 100, 2) : 0.00;
        $scheduledPct = $totalApts > 0 ? round(($scheduledCount / $totalApts) * 100, 2) : 0.00;
        $brokenPct = $totalApts > 0 ? round(($brokenCount / $totalApts) * 100, 2) : 0.00;

        $lifetimeValue = floatval($patientProcedures->sum('ProcFee'));
        $scheduledTP = $patientProcedures->filter(fn($p) => in_array($p->ProcStatus ?? '', ['Scheduled', 'Active', 'Accepted']));
        $unscheduledTP = $patientProcedures->filter(fn($p) => !in_array($p->ProcStatus ?? '', ['Scheduled', 'Active', 'Accepted']));
        $scheduledTPFee = floatval($scheduledTP->sum('ProcFee'));
        $unscheduledTPFee = floatval($unscheduledTP->sum('ProcFee'));

        $guarantorId = $patient->Guarantor ?? null;
        $familyData = [];

        if ($guarantorId) {
            $familyMembers = OdPatient::where('Guarantor', $guarantorId)->get();

            $familyData = $familyMembers->map(function ($m) use ($provMap, $genderMap, $statusMap, $nowStr) {
                $mId = $m->PatNum;
                $mApts = OdAppointment::where('PatNum', $mId)->get();
                $mNext = $mApts->filter(fn($apt) => ($apt->AptDateTime ?? '') >= $nowStr)->sortBy('AptDateTime')->first();
                $mLast = $mApts->filter(fn($apt) => ($apt->AptDateTime ?? '') < $nowStr)->sortByDesc('AptDateTime')->first();

                $mGenderRaw = $m->Gender ?? '';
                $mGender = is_numeric($mGenderRaw) ? ($genderMap[intval($mGenderRaw)] ?? 'Unknown') : ($mGenderRaw ?: 'Unknown');
                $mStatusRaw = $m->PatStatus ?? '';
                $mStatus = is_numeric($mStatusRaw) ? ($statusMap[intval($mStatusRaw)] ?? 'Active') : ($mStatusRaw ?: 'Active');

                return [
                    'name' => ($m->LName ?? '') . ', ' . ($m->FName ?? ''),
                    'status' => $mStatus,
                    'gender' => $mGender,
                    'last_visit' => $mLast ? date('M d, Y', strtotime($mLast->AptDateTime)) : '—',
                    'next_visit' => $mNext ? date('M d, Y', strtotime($mNext->AptDateTime)) : '—',
                    'hygiene_due' => $m->DateRecallDue ?? '—',
                ];
            })->toArray();
        }

        $ledgerItems = [];
        foreach ($patientProcedures->where('ProcStatus', 'C') as $proc) {
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';
            $ledgerItems[] = [
                'code' => $proc->ProcCode ?? ($proc->OldCode ?? '—'),
                'description' => $proc->Descript ?? 'Procedure',
                'tooth' => $proc->ToothNum ?? '',
                'surface' => $proc->Surf ?? '',
                'amount' => '$ ' . number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'date' => isset($proc->ProcDate) ? date('M d, Y', strtotime($proc->ProcDate)) : '—',
                'timestamp' => strtotime($proc->ProcDate ?? ''),
            ];
        }

        usort($ledgerItems, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        $txplansItems = [];
        foreach ($patientProcedures as $proc) {
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $rawStatus = $proc->ProcStatus ?? '';
            $statusText = 'Unscheduled';
            if ($rawStatus === 'C') {
                $statusText = 'Completed';
            } elseif ($rawStatus === 'TP') {
                $statusText = ($proc->AptNum ?? 0) > 0 ? 'Scheduled' : 'Unscheduled';
            } elseif ($rawStatus === 'D') {
                $statusText = 'Deleted';
            }

            $datePlanned = isset($proc->DateTP) && $proc->DateTP !== '0001-01-01'
                ? date('M d, Y', strtotime($proc->DateTP))
                : '—';

            $dateScheduled = '—';
            if (($proc->AptNum ?? 0) > 0) {
                $apt = OdAppointment::where('AptNum', $proc->AptNum)->first();
                if ($apt) {
                    $dateScheduled = date('M d, Y', strtotime($apt->AptDateTime));
                }
            }

            $dateCompleted = $rawStatus === 'C' && isset($proc->ProcDate)
                ? date('M d, Y', strtotime($proc->ProcDate))
                : '—';
            $dateCreated = isset($proc->SecDateEntry)
                ? date('M d, Y', strtotime($proc->SecDateEntry))
                : (isset($proc->DateEntryC) ? date('M d, Y', strtotime($proc->DateEntryC)) : '—');

            $txplansItems[] = [
                'code' => $proc->ProcCode ?? ($proc->OldCode ?? '—'),
                'description' => $proc->Descript ?? 'Procedure',
                'tooth' => $proc->ToothNum ?? '',
                'surface' => $proc->Surf ?? '',
                'amount' => '$ ' . number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'status' => $statusText,
                'planned' => $datePlanned,
                'scheduled' => $dateScheduled,
                'completed' => $dateCompleted,
                'date_created' => $dateCreated,
                'timestamp' => strtotime($proc->ProcDate ?? ''),
            ];
        }
        usort($txplansItems, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // ---- AR Summary Tab (Static Backups Removed) ----
        $arTotal = floatval($patient->BalTotal ?? 0);
        $arInsurance = floatval($patient->InsEst ?? 0);
        $arEstimated = $arTotal - $arInsurance;
        $arCurrent = floatval($patient->Bal_0_30 ?? 0);
        $ar30 = floatval($patient->Bal_31_60 ?? 0);
        $ar60 = floatval($patient->Bal_61_90 ?? 0);
        $ar90 = floatval($patient->BalOver90 ?? 0);

        $employerVal = $patient->EmployerNum ?? null;
        $employer = $employerVal ? "Employer #" . $employerVal : 'No employer information available.';
        $notes = $patient->AddrNote ?? 'No activities or notes available.';

        return response()->json([
            'id' => $patient->PatNum,
            'name' => ($patient->LName ?? '') . ', ' . ($patient->FName ?? ''),
            'age' => $age,
            'gender' => $gender,
            'birthdate' => $birthdateFormatted,
            'status' => $status,
            'mobile_phone' => $patient->WirelessPhone ?: 'N/A',
            'work_phone' => $patient->WkPhone ?: 'N/A',
            'home_phone' => $patient->HmPhone ?: 'N/A',
            'email' => $patient->Email ?: 'N/A',
            'address' => trim(($patient->Address ?? '') . ' ' . ($patient->Address2 ?? '')),
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
            'family' => $familyData,
            'ledger' => $ledgerItems,
            'txplans' => $txplansItems,
            'ar' => [
                'total' => '$ ' . number_format($arTotal, 2),
                'insurance' => '$ ' . number_format($arInsurance, 2),
                'estimated' => '$ ' . number_format($arEstimated, 2),
                'current' => '$ ' . number_format($arCurrent, 2),
                'thirty' => '$ ' . number_format($ar30, 2),
                'sixty' => '$ ' . number_format($ar60, 2),
                'ninety' => '$ ' . number_format($ar90, 2),
                'transactions' => [], // Removed as requested; managed live via showArLive
            ],
            'employer' => $employer,
            'notes' => $notes,
        ]);
    }

    public function showTreatment($patientId)
    {
        $planNums = TreatmentPlan::where('PatNum', $patientId)->pluck('TreatPlanNum');

        $attachedProcNums = OdTreatmentPlanAttachments::whereIn('TreatPlanNum', $planNums)
            ->pluck('ProcNum')
            ->filter()
            ->unique();

        $procedures = $attachedProcNums->isNotEmpty()
            ? OdProcedureLog::whereIn('ProcNum', $attachedProcNums)->get()
            : OdProcedureLog::where('PatNum', $patientId)->get();

        $provMap = OdProvider::all()->pluck('LName', 'ProvNum')->toArray();
        $codeMap = OdProcedure::all()->keyBy('CodeNum');

        $aptNums = $procedures->pluck('AptNum')->filter(fn($aptNum) => ($aptNum ?? 0) > 0)->unique();
        $appointments = OdAppointment::whereIn('AptNum', $aptNums)->get()->keyBy('AptNum');

        $items = $procedures->map(function ($proc) use ($provMap, $codeMap, $appointments) {
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $codeRecord = $codeMap->get($proc->CodeNum);
            $code = $codeRecord->ProcCode ?? ($proc->OldCode ?? '—');
            $description = $codeRecord->Descript ?? 'Procedure';

            $rawStatus = $proc->ProcStatus ?? '';
            $statusText = 'Unscheduled';
            if ($rawStatus === 'C') {
                $statusText = 'Completed';
            } elseif ($rawStatus === 'TP') {
                $statusText = ($proc->AptNum ?? 0) > 0 ? 'Scheduled' : 'Unscheduled';
            } elseif ($rawStatus === 'D') {
                $statusText = 'Deleted';
            }

            $datePlanned = isset($proc->DateTP) && $proc->DateTP !== '0001-01-01'
                ? date('M d, Y', strtotime($proc->DateTP))
                : '—';

            $dateScheduled = '—';
            if (($proc->AptNum ?? 0) > 0 && $appointments->has($proc->AptNum)) {
                $dateScheduled = date('M d, Y', strtotime($appointments[$proc->AptNum]->AptDateTime));
            }

            $dateCompleted = $rawStatus === 'C' && isset($proc->ProcDate)
                ? date('M d, Y', strtotime($proc->ProcDate))
                : '—';

            $dateCreated = isset($proc->SecDateEntry)
                ? date('M d, Y', strtotime($proc->SecDateEntry))
                : (isset($proc->DateEntryC) ? date('M d, Y', strtotime($proc->DateEntryC)) : '—');

            return [
                'code' => $code,
                'description' => $description,
                'tooth' => $proc->ToothNum ?? '',
                'surface' => $proc->Surf ?? '',
                'amount' => '$ ' . number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
                'status' => $statusText,
                'date_planned' => $datePlanned,
                'date_scheduled' => $dateScheduled,
                'date_completed' => $dateCompleted,
                'date_created' => $dateCreated,
                'timestamp' => strtotime($proc->ProcDate ?? ''),
            ];
        })
            ->sortByDesc('timestamp')
            ->values()
            ->map(fn($item) => collect($item)->except('timestamp')->all());

        return response()->json($items);
    }

    public function showAR($patientId)
    {
        $ar = OdPatientBalance::where('PatNum', $patientId)->first();

        if (!$ar) {
            return response()->json([
                'total' => 0,
                'insurance_claims' => 0,
                'estimated_patient' => 0,
                'aging' => [
                    'current' => 0,
                    '30_days' => 0,
                    '60_days' => 0,
                    '90_days' => 0,
                ]
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
            ]
        ]);
    }

    public function showArLive($patientId)
    {
        // Fetch Live Aging Array from Service
        $ar = $this->ar->aging($patientId);

        // Fetch Live completed transactions for the sub-log
        $patientProcedures = OdProcedureLog::where('PatNum', $patientId)->where('ProcStatus', 'C')->get();
        $provMap = OdProvider::all()->pluck('LName', 'ProvNum')->toArray();

        $arTransactions = [];
        foreach ($patientProcedures as $proc) {
            if (floatval($proc->ProcFee ?? 0) <= 0) {
                continue;
            }
            $provNum = $proc->ProvNum ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $arTransactions[] = [
                'description' => $proc->Descript ?? 'Procedure',
                'code' => $proc->ProcCode ?? ($proc->OldCode ?? '—'),
                'amount' => '$ ' . number_format(floatval($proc->ProcFee ?? 0), 2),
                'provider' => $provName,
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
            'transactions' => $arTransactions
        ]);
    }
}