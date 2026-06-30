<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Services\OpenDental\PatientService;
use App\Services\OpenDental\AppointmentService;
use App\Services\OpenDental\ProcedureService;
use App\Services\OpenDental\TreatmentPlanService;
use App\Services\OpenDental\PaymentService;
use App\Services\OpenDental\ProviderService;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{

    public function index()
    {

        return view('patients.index');

    }
    // public function index2(
//     Request $request,

    //     PatientService $patients,
//     AppointmentService $appointments,
//     ProcedureService $procedures

    // ){


    //     $refresh = $request->boolean('refresh', false);



    //     /*
//     |--------------------------------------------------------------------------
//     | Patients
//     |--------------------------------------------------------------------------
//     */

    //     $allPatients = $this->getCachedApi(
//         'od_patients',
//         fn() => $patients->all(),
//         $refresh
//     );



    //     /*
//     |--------------------------------------------------------------------------
//     | Appointments
//     |--------------------------------------------------------------------------
//     */

    //     $allAppointments = $this->getCachedApi(
//         'od_appointments',
//         fn() => $appointments->all(),
//         $refresh
//     );



    //     /*
//     |--------------------------------------------------------------------------
//     | Procedures
//     |--------------------------------------------------------------------------
//     */

    //     $allProcedures = $this->getCachedApi(
//         'od_procedures',
//         fn() => $procedures->all(),
//         $refresh
//     );

    //     /*
//     |--------------------------------------------------------------------------
//     | Analytics (temporary)
//     |--------------------------------------------------------------------------
//     */


    //     $data = collect($allPatients)
//         ->map(function($patient) use(
//             $allAppointments,
//             $allProcedures
//         ){


    //             $patientId = $patient['PatNum'];



    //             $patientAppointments = collect($allAppointments)
//                 ->where(
//                     'PatNum',
//                     $patientId
//                 );



    //             $patientProcedures = collect($allProcedures)
//                 ->where(
//                     'PatNum',
//                     $patientId
//                 );



    //             return [

    //                 'patient_id'=>$patientId,


    //                 'name'=>
//                     ($patient['FName'] ?? '')
//                     .' '
//                     .($patient['LName'] ?? ''),

    //                 'phone'=>$patient['WirelessPhone'] ?? null,

    //                 'email'=>$patient['Email'] ?? null,

    //                 'birthdate'=>date('d M Y', strtotime($patient['Birthdate'] ?? null)),

    //                 'address'=>
//                     ($patient['Address'] ?? '')
//                     .' '
//                     .($patient['Address2'] ?? ''),

    //                 'city'=>$patient['City'] ?? null,
//                 'zip'=>$patient['Zip'] ?? null,
//                 'state'=>$patient['State'] ?? null,


    //                 'first_visit'=>
//                     $patientAppointments
//                     ->sortBy('AptDateTime')
//                     ->first()['AptDateTime'] ?? null,



    //                 'last_visit'=>
//                     $patientAppointments
//                     ->sortByDesc('AptDateTime')
//                     ->first()['AptDateTime'] ?? null,



    //                 'lifetime_production'=>
//                     floatval($patientProcedures
//                     ->sum('ProcFee'))

    //             ];


    //         });



    //     return view('patients.index',['data'=>$data]);


    // }

    public function data(

        PatientService $patients,
        AppointmentService $appointments,
        ProcedureService $procedures

    ) {


        $allPatients = $this->getCachedApi(
            'od_patients',
            fn() => $patients->all()
        );



        $allAppointments = $this->getCachedApi(
            'od_appointments',
            fn() => $appointments->all()
        );



        $allProcedures = $this->getCachedApi(
            'od_procedures',
            fn() => $procedures->all()
        );



        $data = collect($allPatients)
            ->map(function ($patient) use ($allAppointments, $allProcedures) {



                $patientId = $patient['PatNum'];



                $patientAppointments =
                    collect($allAppointments)
                        ->where('PatNum', $patientId);



                $patientProcedures =
                    collect($allProcedures)
                        ->where('PatNum', $patientId);



                return [


                    'id' => $patientId,
                    'patient_id' => $patientId,


                    'name' => ($patient['LName'] ?? '') . ' ' . ($patient['FName'] ?? ''),


                    'phone' => $patient['WirelessPhone'] ?? '',


                    'email' => $patient['Email'] ?? '',


                    'birthdate' => $patient['Birthdate'] ?? '',


                    'address' =>
                        ($patient['Address'] ?? '')
                        . ' '
                        . ($patient['Address2'] ?? ''),


                    'city' => $patient['City'] ?? '',


                    'state' => $patient['State'] ?? '',


                    'zip' => $patient['Zip'] ?? '',



                    'first_visit' =>

                        $patientAppointments
                            ->sortBy('AptDateTime')
                            ->first()['AptDateTime'] ?? null,



                    'last_visit' =>

                        $patientAppointments
                            ->sortByDesc('AptDateTime')
                            ->first()['AptDateTime'] ?? null,



                    'lifetime_production' =>

                        $patientProcedures
                            ->sum('ProcFee')


                ];



            });




        return DataTables::of($data)->make(true);
        // return response()->json(["data"=>$data]);

    }

    public function show(
        $id,
        PatientService $patients,
        AppointmentService $appointments,
        ProcedureService $procedures,
        TreatmentPlanService $treatments,
        PaymentService $payments,
        ProviderService $providers
    ) {
        try {
            $patient = $patients->find($id);
        } catch (\Exception $e) {
            $allPatients = $this->getCachedApi('od_patients', fn() => $patients->all());
            $patient = collect($allPatients)->firstWhere('PatNum', $id);
        }

        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $dobStr = $patient['Birthdate'] ?? null;
        $age = 'N/A';
        $birthdateFormatted = 'N/A';
        if ($dobStr && $dobStr !== '0001-01-01' && date_create($dobStr)) {
            $dob = new \DateTime($dobStr);
            $age = $dob->diff(new \DateTime())->y;
            $birthdateFormatted = $dob->format('M d, Y');
        }

        $genderMap = [0 => 'Male', 1 => 'Female', 2 => 'Unknown'];
        $genderRaw = $patient['Gender'] ?? '';
        $gender = is_numeric($genderRaw) ? ($genderMap[intval($genderRaw)] ?? 'Unknown') : ($genderRaw ?: 'Unknown');

        $statusMap = [0 => 'Active', 1 => 'NonPatient', 2 => 'Inactive', 3 => 'Archived', 4 => 'Deceased', 5 => 'Prospective'];
        $statusRaw = $patient['PatStatus'] ?? '';
        $status = is_numeric($statusRaw) ? ($statusMap[intval($statusRaw)] ?? 'Active') : ($statusRaw ?: 'Active');

        // Parallel-like caching and fetching
        $allAppointments = $this->getCachedApi('od_appointments', fn() => $appointments->all());
        $patientAppointments = collect($allAppointments)->where('PatNum', $id);

        $allProcedures = $this->getCachedApi('od_procedures', fn() => $procedures->all());
        $patientProcedures = collect($allProcedures)->where('PatNum', $id);

        $allTreatments = $this->getCachedApi('od_treatment_plans', fn() => $treatments->all());
        $patientTreatments = collect($allTreatments)->where('PatNum', $id);

        $allPayments = $this->getCachedApi('od_payments', fn() => $payments->all());
        $patientPayments = collect($allPayments)->where('PatNum', $id);

        $allProviders = $this->getCachedApi('od_providers', fn() => $providers->all());
        $provMap = collect($allProviders)->pluck('LName', 'ProvNum')->toArray();

        $allPatients = $this->getCachedApi('od_patients', fn() => $patients->all());

        // Next & Last Appointment
        $nowStr = now()->format('Y-m-d H:i:s');

        $nextApt = $patientAppointments
            ->filter(fn($apt) => ($apt['AptDateTime'] ?? '') >= $nowStr)
            ->sortBy('AptDateTime')
            ->first();

        $lastApt = $patientAppointments
            ->filter(fn($apt) => ($apt['AptDateTime'] ?? '') < $nowStr)
            ->sortByDesc('AptDateTime')
            ->first();

        $completedCount = $patientAppointments->filter(fn($apt) => in_array($apt['AptStatus'] ?? '', [2, 'Complete', 'Completed']))->count();
        $scheduledCount = $patientAppointments->filter(fn($apt) => in_array($apt['AptStatus'] ?? '', [1, 'Scheduled', 'Active', 'Scheduled/Active']))->count();
        $brokenCount = $patientAppointments->filter(fn($apt) => in_array($apt['AptStatus'] ?? '', [5, 'Broken']))->count();
        $totalApts = $completedCount + $scheduledCount + $brokenCount;

        $completedPct = $totalApts > 0 ? round(($completedCount / $totalApts) * 100, 2) : 0.00;
        $scheduledPct = $totalApts > 0 ? round(($scheduledCount / $totalApts) * 100, 2) : 0.00;
        $brokenPct = $totalApts > 0 ? round(($brokenCount / $totalApts) * 100, 2) : 0.00;

        $lifetimeValue = floatval($patientProcedures->sum('ProcFee'));
        $scheduledTP = $patientTreatments->filter(fn($tp) => in_array($tp['Status'] ?? '', ['Scheduled', 'Active', 'Accepted']));
        $unscheduledTP = $patientTreatments->filter(fn($tp) => !in_array($tp['Status'] ?? '', ['Scheduled', 'Active', 'Accepted']));
        $scheduledTPFee = floatval($scheduledTP->sum('Fee'));
        $unscheduledTPFee = floatval($unscheduledTP->sum('Fee'));

        // ---- Family Tab ----
        $guarantorId = $patient['Guarantor'] ?? null;
        $familyData = [];
        if ($guarantorId) {
            $familyMembers = collect($allPatients)
                ->where('Guarantor', $guarantorId)
                ->values();

            $familyData = collect($familyMembers)->map(function ($m) use ($allAppointments, $genderMap, $statusMap) {
                $mId = $m['PatNum'];
                $mApts = collect($allAppointments)->where('PatNum', $mId);
                $nowStr = now()->format('Y-m-d H:i:s');
                $mNext = $mApts->filter(fn($apt) => ($apt['AptDateTime'] ?? '') >= $nowStr)->sortBy('AptDateTime')->first();
                $mLast = $mApts->filter(fn($apt) => ($apt['AptDateTime'] ?? '') < $nowStr)->sortByDesc('AptDateTime')->first();

                $mGenderRaw = $m['Gender'] ?? '';
                $mGender = is_numeric($mGenderRaw) ? ($genderMap[intval($mGenderRaw)] ?? 'Unknown') : ($mGenderRaw ?: 'Unknown');
                $mStatusRaw = $m['PatStatus'] ?? '';
                $mStatus = is_numeric($mStatusRaw) ? ($statusMap[intval($mStatusRaw)] ?? 'Active') : ($mStatusRaw ?: 'Active');

                return [
                    'name' => ($m['LName'] ?? '') . ', ' . ($m['FName'] ?? ''),
                    'status' => $mStatus,
                    'gender' => $mGender,
                    'last_visit' => $mLast ? date('M d, Y', strtotime($mLast['AptDateTime'])) : '—',
                    'next_visit' => $mNext ? date('M d, Y', strtotime($mNext['AptDateTime'])) : '—',
                    'hygiene_due' => $m['DateRecallDue'] ?? '—',
                ];
            })->toArray();
        }

        // ---- Ledger Tab ----
        $ledgerItems = [];
        foreach ($patientProcedures->where('ProcStatus', 'C') as $proc) {
            $provNum = $proc['ProvNum'] ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';
            $ledgerItems[] = [
                'code' => $proc['ProcCode'] ?? '—',
                'description' => $proc['ProcDescript'] ?? ($proc['Descript'] ?? 'Procedure'),
                'tooth' => $proc['ToothNum'] ?? '',
                'surface' => $proc['Surf'] ?? '',
                'amount' => '$ ' . number_format(floatval($proc['ProcFee'] ?? 0), 2),
                'provider' => $provName,
                'date' => isset($proc['ProcDate']) ? date('M d, Y', strtotime($proc['ProcDate'])) : (isset($proc['ProcDateTime']) ? date('M d, Y', strtotime($proc['ProcDateTime'])) : '—'),
                'timestamp' => strtotime($proc['ProcDate'] ?? ($proc['ProcDateTime'] ?? ''))
            ];
        }

        foreach ($patientPayments as $pay) {
            $ledgerItems[] = [
                'code' => 'PAY',
                'description' => 'Patient Payment' . (isset($pay['PayType']) ? ' (' . $pay['PayType'] . ')' : ''),
                'tooth' => '',
                'surface' => '',
                'amount' => '-$ ' . number_format(floatval($pay['PayAmt'] ?? 0), 2),
                'provider' => '—',
                'date' => isset($pay['PayDate']) ? date('M d, Y', strtotime($pay['PayDate'])) : (isset($pay['DateEntry']) ? date('M d, Y', strtotime($pay['DateEntry'])) : '—'),
                'timestamp' => strtotime($pay['PayDate'] ?? ($pay['DateEntry'] ?? ''))
            ];
        }
        usort($ledgerItems, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // ---- TX Plans Tab ----
        $txplansItems = [];
        foreach ($patientProcedures as $proc) {
            $provNum = $proc['ProvNum'] ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';

            $rawStatus = $proc['ProcStatus'] ?? '';
            $statusText = 'Unscheduled';
            if ($rawStatus === 'C') {
                $statusText = 'Completed';
            } elseif ($rawStatus === 'TP') {
                $statusText = ($proc['AptNum'] ?? 0) > 0 ? 'Scheduled' : 'Unscheduled';
            } elseif ($rawStatus === 'D') {
                $statusText = 'Deleted';
            }

            $datePlanned = isset($proc['DateTP']) && $proc['DateTP'] !== '0001-01-01' ? date('M d, Y', strtotime($proc['DateTP'])) : '—';

            $dateScheduled = '—';
            if (($proc['AptNum'] ?? 0) > 0) {
                $apt = collect($allAppointments)->firstWhere('AptNum', $proc['AptNum']);
                if ($apt) {
                    $dateScheduled = date('M d, Y', strtotime($apt['AptDateTime']));
                }
            }

            $dateCompleted = $rawStatus === 'C' && isset($proc['ProcDate']) ? date('M d, Y', strtotime($proc['ProcDate'])) : '—';
            $dateCreated = isset($proc['DateEntry']) ? date('M d, Y', strtotime($proc['DateEntry'])) : '—';

            $txplansItems[] = [
                'code' => $proc['ProcCode'] ?? '—',
                'description' => $proc['ProcDescript'] ?? ($proc['Descript'] ?? 'Procedure'),
                'tooth' => $proc['ToothNum'] ?? '',
                'surface' => $proc['Surf'] ?? '',
                'amount' => '$ ' . number_format(floatval($proc['ProcFee'] ?? 0), 2),
                'provider' => $provName,
                'status' => $statusText,
                'planned' => $datePlanned,
                'scheduled' => $dateScheduled,
                'completed' => $dateCompleted,
                'date_created' => $dateCreated,
                'timestamp' => strtotime($proc['ProcDate'] ?? ($proc['ProcDateTime'] ?? ''))
            ];
        }
        usort($txplansItems, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // ---- AR Summary Tab ----
        $arTotal = floatval($patient['BalTotal'] ?? 0);
        $arInsurance = floatval($patient['InsEst'] ?? 0);
        $arEstimated = $arTotal - $arInsurance;
        $arCurrent = floatval($patient['Bal_0_30'] ?? 0);
        $ar30 = floatval($patient['Bal_31_60'] ?? 0);
        $ar60 = floatval($patient['Bal_61_90'] ?? 0);
        $ar90 = floatval($patient['Bal_Over90'] ?? 0);

        $arTransactions = [];
        foreach ($patientProcedures->where('ProcStatus', 'C') as $proc) {
            if (floatval($proc['ProcFee'] ?? 0) <= 0)
                continue;
            $provNum = $proc['ProvNum'] ?? null;
            $provName = $provNum && isset($provMap[$provNum]) ? $provMap[$provNum] : '—';
            $arTransactions[] = [
                'description' => $proc['ProcDescript'] ?? ($proc['Descript'] ?? 'Procedure'),
                'code' => $proc['ProcCode'] ?? '—',
                'amount' => '$ ' . number_format(floatval($proc['ProcFee'] ?? 0), 2),
                'provider' => $provName,
                'date' => isset($proc['ProcDate']) ? date('M d, Y', strtotime($proc['ProcDate'])) : '—',
            ];
        }

        // Employer
        $employerVal = $patient['Employer'] ?? ($patient['EmployerNum'] ?? null);
        $employer = $employerVal ? (is_numeric($employerVal) ? "Employer #" . $employerVal : $employerVal) : 'No employer information available.';

        // Address notes / general notes
        $notes = $patient['PatNote'] ?? 'No activities or notes available.';

        return response()->json([
            'id' => $patient['PatNum'],
            'name' => ($patient['LName'] ?? '') . ', ' . ($patient['FName'] ?? ''),
            'age' => $age,
            'gender' => $gender,
            'birthdate' => $birthdateFormatted,
            'status' => $status,
            'mobile_phone' => $patient['WirelessPhone'] ?: 'N/A',
            'work_phone' => $patient['WkPhone'] ?: 'N/A',
            'home_phone' => $patient['HmPhone'] ?: 'N/A',
            'email' => $patient['Email'] ?: 'N/A',
            'address' => trim(($patient['Address'] ?? '') . ' ' . ($patient['Address2'] ?? '')),
            'city' => $patient['City'] ?? '',
            'state' => $patient['State'] ?? '',
            'zip' => $patient['Zip'] ?? '',
            'overview' => [
                'next_visit' => [
                    'date' => $nextApt ? date('M d, Y', strtotime($nextApt['AptDateTime'])) : '-',
                    'fee' => 0.00,
                    'label' => $nextApt['ProcDescript'] ?? 'N/A',
                ],
                'last_visit' => [
                    'date' => $lastApt ? date('M d, Y', strtotime($lastApt['AptDateTime'])) : '-',
                    'fee' => 0.00,
                    'label' => $lastApt['ProcDescript'] ?? 'N/A',
                ],
                'remaining_insurance' => 0.00,
                'treatment_plans' => [
                    'scheduled' => $scheduledTPFee,
                    'unscheduled' => $unscheduledTPFee,
                ],
                'hygiene_due' => $patient['DateRecallDue'] ?? '—',
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
                ]
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
                'transactions' => $arTransactions,
            ],
            'employer' => $employer,
            'notes' => $notes,
        ]);
    }

    private function getCachedApi(
        $key,
        $callback,
        $refresh = false
    ) {

        if ($refresh) {

            Cache::forget($key);

        }


        return Cache::remember(
            $key,

            now()->addHours(12),

            $callback
        );

    }



}