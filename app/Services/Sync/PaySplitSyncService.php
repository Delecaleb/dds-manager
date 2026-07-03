<?php

namespace App\Services\Sync;

use App\Models\OdPaySplit;
use App\Models\SyncLog;
use App\Services\OpenDental\PaySplitService;

class PaySplitSyncService
{
    public function __construct(
        protected PaySplitService $api
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate(
            ['module' => 'paysplits'],
            ['last_synced_at' => now()]
        );

        $page = 0;

        do {

            $splits = $this->api->all([
                'Offset' => $page * 1000
            ]);

            foreach ($splits as $split) {

                OdPaySplit::updateOrCreate(

                    [
                        'SplitNum' => $split['SplitNum']
                    ],

                    [
                        'SplitAmt' => $split['SplitAmt'],
                        'PatNum' => $split['PatNum'],
                        'ProcDate' => $split['ProcDate'],
                        'PayNum' => $split['PayNum'],
                        'IsDiscount' => $split['IsDiscount'],
                        'DiscountType' => $split['DiscountType'],
                        'ProvNum' => $split['ProvNum'],
                        'PayPlanNum' => $split['PayPlanNum'],
                        'DatePay' => $split['DatePay'],
                        'ProcNum' => $split['ProcNum'],
                        'DateEntry' => $split['DateEntry'],
                        'UnearnedType' => $split['UnearnedType'],
                        'ClinicNum' => $split['ClinicNum'],
                        'SecUserNumEntry' => $split['SecUserNumEntry'],
                        'SecDateTEdit' => $split['SecDateTEdit'],
                        'FSplitNum' => $split['FSplitNum'],
                        'AdjNum' => $split['AdjNum'],
                        'PayPlanChargeNum' => $split['PayPlanChargeNum'],
                        'PayPlanDebitType' => $split['PayPlanDebitType'],
                        'SecurityHash' => $split['SecurityHash']
                    ]
                );

            }

            $page++;

        } while (count($splits) === 1000);

        $log->update([
            'last_synced_at' => now()
        ]);
    }
}