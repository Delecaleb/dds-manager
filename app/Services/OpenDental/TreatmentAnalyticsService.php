<?php

namespace App\Services\OpenDental;

class TreatmentAnalyticsService
{
    public function __construct(

        protected TreatmentPlanService $treatments,

        protected ProcedureService $procedures,

        protected ProviderService $providers,

        protected ProcedureCodeService $codes

    ) {}

    public function patientPlans($patientId)
    {
        /*
        |--------------------------------------------------------------------------
        | Get patient's treatment plans
        |--------------------------------------------------------------------------
        */

        $plans = collect(

            $this->treatments->find($patientId)

        );

        $providers = collect(

            $this->providers->all()

        );

        $data = collect();

        foreach ($plans as $plan) {

            $attachments = collect(

                $this->treatments->attach(

                    $plan['TreatPlanNum']

                )

            );

            foreach ($attachments as $attach) {

                $procedure = $this->procedures->find(

                    $attach['ProcNum']

                );

                if (! $procedure) {

                    continue;

                }
                $provider = $providers->firstWhere(

                    'ProvNum',

                    $procedure['ProvNum']

                );

                $code = $this->codes->find(

                    $procedure['CodeNum']

                );
                $data->push([
                    'treatment_plan_id' => $plan['TreatPlanNum'],
                    'date_planned' => $procedure['ProcDate'] ?? null,

                    'code' => $code['ProcCode'] ?? null,

                    'description' => $code['Descript'] ?? null,

                    'provider_id' => $procedure['ProvNum'] ?? null,

                    'provider' => ($provider['FName'] ?? '')
                        .' '
                        .($provider['LName'] ?? ''),

                    'provider_abbr' => $provider['Abbr'] ?? null,

                    'status' => $this->status(

                        $procedure['ProcStatus'] ?? null

                    ),
                    'amount' => floatval(

                        $procedure['ProcFee'] ?? 0

                    ),
                    'date_completed' => ($procedure['ProcStatus'] ?? null)
                        === 'C'

                        ?

                        $procedure['ProcDate']

                        :

                        '--',
                    'date_scheduled' => $procedure['AptNum']
                        ??

                        null,
                ]);
            }
        }

        return $data;
    }

    private function status($status)
    {
        return match ($status) {

            'TP' => 'Scheduled',

            'C' => 'Completed',

            'D' => 'Deleted',

            default => 'Pending'

        };

    }
}
