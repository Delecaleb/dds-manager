<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Services\OpenDental\PatientService;
use App\Services\OpenDental\AppointmentService;
use App\Services\OpenDental\ProcedureService;
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

){


$allPatients = $this->getCachedApi(
    'od_patients',
    fn()=> $patients->all()
);



$allAppointments = $this->getCachedApi(
    'od_appointments',
    fn()=> $appointments->all()
);



$allProcedures = $this->getCachedApi(
    'od_procedures',
    fn()=> $procedures->all()
);



$data = collect($allPatients)
->map(function($patient) use(
    $allAppointments,
    $allProcedures
){



$patientId=$patient['PatNum'];



$patientAppointments =
collect($allAppointments)
->where('PatNum',$patientId);



$patientProcedures =
collect($allProcedures)
->where('PatNum',$patientId);



return [


'id'=>$patientId,


'name'=> ($patient['LName']??'').' '.($patient['FName']??''),


'phone'=>$patient['WirelessPhone']??'',


'email'=>$patient['Email']??'',


'birthdate'=>$patient['Birthdate']??'',


'address'=>
($patient['Address']??'')
.' '
.($patient['Address2']??''),


'city'=>$patient['City']??'',


'state'=>$patient['State']??'',


'zip'=>$patient['Zip']??'',



'first_visit'=>

$patientAppointments
->sortBy('AptDateTime')
->first()['AptDateTime'] ?? null,



'last_visit'=>

$patientAppointments
->sortByDesc('AptDateTime')
->first()['AptDateTime'] ?? null,



'lifetime_production'=>

$patientProcedures
->sum('ProcFee')


];



});




return DataTables::of($data)->make(true);
// return response()->json(["data"=>$data]);

}


private function getCachedApi(
    $key,
    $callback,
    $refresh=false
){

    if($refresh){

        Cache::forget($key);

    }


    return Cache::remember(
        $key,

        now()->addHours(12),

        $callback
    );

}



}