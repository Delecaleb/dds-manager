<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Synced tables with their corresponding primary key column.
     *
     * @var array<string, string>
     */
    protected array $tableKeys = [
        'od_patients' => 'PatNum',
        'od_providers' => 'ProvNum',
        'od_patient_balances' => 'PatNum',
        'od_appointments' => 'AptNum',
        'od_procedure_logs' => 'ProcNum',
        'od_procedures' => 'CodeNum',
        'od_adjustments' => 'AdjNum',
        'od_carriers' => 'CarrierNum',
        'od_claim_payments' => 'ClaimPaymentNum',
        'od_clinics' => 'ClinicNum',
        'od_definitions' => 'DefNum',
        'od_deposits' => 'DepositNum',
        'od_insplans' => 'PlanNum',
        'od_pay_plan_charges' => 'PayPlanChargeNum',
        'od_payments' => 'PayNum',
        'od_recalls' => 'RecallNum',
        'od_recall_types' => 'RecallTypeNum',
        'od_schedule' => 'ScheduleNum',
        'od_treatment_plan_attachments' => 'TreatPlanAttachNum',
        'od_claim_procs' => 'ClaimProcNum',
        'claim_procs' => 'ClaimProcNum',
        'od_pay_splits' => 'SplitNum',
        'pay_splits' => 'SplitNum',
        'sync_logs' => 'id',
        'treatment_plans' => 'TreatPlanNum',
        'account_modules' => 'id',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tableKeys as $tableName => $pk) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $addedOfficeId = false;

            // 1. Ensure office_id column exists
            if (! Schema::hasColumn($tableName, 'office_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('office_id')->default(1)->index();
                });
                $addedOfficeId = true;
            }

            // 2. If office_id was missing and newly added by this migration, apply composite unique index
            if ($addedOfficeId && Schema::hasColumn($tableName, $pk) && $pk !== 'id') {
                $indexName = "{$tableName}_office_".strtolower($pk).'_unique';

                // Clean up duplicates if needed
                if (Schema::hasColumn($tableName, 'id')) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) use ($pk) {
                            $table->index($pk);
                        });
                    } catch (Throwable $e) {
                        // Index may already exist
                    }

                    try {
                        DB::statement("
                            DELETE t1 FROM `{$tableName}` t1
                            INNER JOIN `{$tableName}` t2
                            ON t1.`office_id` = t2.`office_id`
                            AND t1.`{$pk}` = t2.`{$pk}`
                            WHERE t1.`id` > t2.`id`
                              AND t1.`{$pk}` IS NOT NULL
                        ");
                    } catch (Throwable $e) {
                        // Ignore error if multi-table delete syntax not supported
                    }
                }

                try {
                    Schema::table($tableName, function (Blueprint $table) use ($pk, $indexName) {
                        $table->unique(['office_id', $pk], $indexName);
                    });
                } catch (Throwable $e) {
                    // Index might already exist
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed on rollback to avoid destructive data loss
    }
};
