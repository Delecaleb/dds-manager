<?php

namespace App\Services\Sync;

use App\Models\OdProvider;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class ProviderSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'providers'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('providers', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $provider) {
                OdProvider::updateOrCreate(
                    ['ProvNum' => $provider['ProvNum']],
                    [
                        'Abbr' => $provider['Abbr'] ?? null,
                        'ItemOrder' => $provider['ItemOrder'] ?? null,
                        'LName' => $provider['LName'] ?? null,
                        'PName' => $provider['PName'] ?? null,
                        'MI' => $provider['MI'] ?? null,
                        'Suffix' => $provider['Suffix'] ?? null,
                        'FeeSched' => $provider['FeeSched'] ?? null,
                        'Specialty' => $provider['Specialty'] ?? null,
                        'SSN' => $provider['SSN'] ?? null,
                        'StateLicense' => $provider['StateLicense'] ?? null,
                        'DEANum' => $provider['DEANum'] ?? null,
                        'IsSecondary' => $provider['IsSecondary'] ?? null,
                        'ProvColor' => $provider['ProvColor'] ?? null,
                        'IsHidden' => $provider['IsHidden'] ?? null,
                        'UsingTIN' => $provider['UsingTIN'] ?? null,
                        'BlueCrossID' => $provider['BlueCrossID'] ?? null,
                        'SigOnFile' => $provider['SigOnFile'] ?? null,
                        'MedicaidID' => $provider['MedicaidID'] ?? null,
                        'OutlineColor' => $provider['OutlineColor'] ?? null,
                        'SchoolClassNum' => $provider['SchoolClassNum'] ?? null,
                        'NationalProvID' => $provider['NationalProvID'] ?? null,
                        'CanadianOfficeNum' => $provider['CanadianOfficeNum'] ?? null,
                        'DateTStamp' => $provider['DateTStamp'] ?? null,
                        'AnesthProvType' => $provider['AnesthProvType'] ?? null,
                        'TaxonomyCodeOverride' => $provider['TaxonomyCodeOverride'] ?? null,
                        'IsCDAnet' => $provider['IsCDAnet'] ?? null,
                        'EcwID' => $provider['EcwID'] ?? null,
                        'StateRxID' => $provider['StateRxID'] ?? null,
                        'IsNotPerson' => $provider['IsNotPerson'] ?? null,
                        'StateWhereLicensed' => $provider['StateWhereLicensed'] ?? null,
                        'EmailAddressNum' => $provider['EmailAddressNum'] ?? null,
                        'IsInstructor' => $provider['IsInstructor'] ?? null,
                        'EhrMuStage' => $provider['EhrMuStage'] ?? null,
                        'ProvNumBillingOverride' => $provider['ProvNumBillingOverride'] ?? null,
                        'CustomID' => $provider['CustomID'] ?? null,
                        'ProvStatus' => $provider['ProvStatus'] ?? null,
                        'IsHiddenReport' => $provider['IsHiddenReport'] ?? null,
                        'IsErxEnabled' => $provider['IsErxEnabled'] ?? null,
                        'Birthdate' => $provider['Birthdate'] ?? null,
                        'SchedNote' => $provider['SchedNote'] ?? null,
                        'WebSchedDescript' => $provider['WebSchedDescript'] ?? null,
                        'WebSchedFaceT' => $provider['WebSchedFaceT'] ?? null,
                        'WebSchedImageLocation' => $provider['WebSchedImageLocation'] ?? null,
                        'HourlyProdGoalAmt' => $provider['HourlyProdGoalAmt'] ?? null,
                        'DateTerm' => $provider['DateTerm'] ?? null,
                        'PreferredName' => $provider['PreferredName'] ?? null,
                    ]
                );
            }

            if (count($response) < 1000) {
                break;
            }

            $offset += 1000;
        }

        $log->update([
            'last_synced_at' => now()
        ]);
    }
}
