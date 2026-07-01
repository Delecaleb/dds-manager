<?php

namespace App\Services\Sync;


use App\Models\OdPatient;
use App\Models\SyncLog; // my table storing sync last time
use App\Services\OpenDental\PatientService;


class PatientSyncService
{

    public function __construct(
        protected PatientService $api
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'patients'
        ]);

        $params = [];

        if ($log->last_synced_at) {
            $params['SecDateTEdit'] = $log->last_synced_at;
        }

        $patients = $this->api->allPaginated();

        foreach ($patients as $patient) {
            OdPatient::updateOrCreate(
                ['PatNum' => $patient['PatNum']],
                [
                    'FName' => $patient['FName'] ?? null,
                    'LName' => $patient['LName'] ?? null,
                    'Email' => $patient['Email'] ?? null,
                    'Birthdate' => $patient['Birthdate'] ?? null,
                    'MiddleI' => $patient['MiddleI'] ?? null,
                    'Preferred' => $patient['Preferred'] ?? null,
                    'PatStatus' => $patient['PatStatus'] ?? null,
                    'Gender' => $patient['Gender'] ?? null,
                    'Position' => $patient['Position'] ?? null,
                    'SSN' => $patient['SSN'] ?? null,
                    'Address' => $patient['Address'] ?? null,
                    'Address2' => $patient['Address2'] ?? null,
                    'City' => $patient['City'] ?? null,
                    'State' => $patient['State'] ?? null,
                    'Zip' => $patient['Zip'] ?? null,
                    'HmPhone' => $patient['HmPhone'] ?? null,
                    'WkPhone' => $patient['WkPhone'] ?? null,
                    'WirelessPhone' => $patient['WirelessPhone'] ?? null,
                    'Guarantor' => $patient['Guarantor'] ?? null,
                    'CreditType' => $patient['CreditType'] ?? null,
                    'Salutation' => $patient['Salutation'] ?? null,
                    'PriProv' => $patient['PriProv'] ?? null,
                    'SecProv' => $patient['SecProv'] ?? null,
                    'FeeSched' => $patient['FeeSched'] ?? null,
                    'BillingType' => $patient['BillingType'] ?? null,
                    'ImageFolder' => $patient['ImageFolder'] ?? null,
                    'AddrNote' => $patient['AddrNote'] ?? null,
                    'FamFinUrgNote' => $patient['FamFinUrgNote'] ?? null,
                    'MedUrgNote' => $patient['MedUrgNote'] ?? null,
                    'ApptModNote' => $patient['ApptModNote'] ?? null,
                    'Fac' => $patient['Fac'] ?? null,
                    'StudentStatus' => $patient['StudentStatus'] ?? null,
                    'SchoolName' => $patient['SchoolName'] ?? null,
                    'ChartNumber' => $patient['ChartNumber'] ?? null,
                    'MedicaidID' => $patient['MedicaidID'] ?? null,
                    'Bal_0_30' => $patient['Bal_0_30'] ?? null,
                    'Bal_31_60' => $patient['Bal_31_60'] ?? null,
                    'Bal_61_90' => $patient['Bal_61_90'] ?? null,
                    'BalOver90' => $patient['BalOver90'] ?? null,
                    'InsEst' => $patient['InsEst'] ?? null,
                    'BalTotal' => $patient['BalTotal'] ?? null,
                    'EmployerNum' => $patient['EmployerNum'] ?? null,
                    'EmploymentNote' => $patient['EmploymentNote'] ?? null,
                    'County' => $patient['County'] ?? null,
                    'GradeLevel' => $patient['GradeLevel'] ?? null,
                ]


            );



        }

        $log->update([

            'last_synced_at' => now()

        ]);


    }

}