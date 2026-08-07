<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Synced tables with their corresponding OpenDental primary key column.
     *
     * @var array<string, string>
     */
    protected array $tableKeys = [
        'od_patients' => 'PatNum',
        'od_providers' => 'ProvNum',
        'od_patient_balances' => 'PatNum',
        'od_appointments' => 'AptNum',
        'od_procedure_logs' => 'ProcNum',
        'od_adjustments' => 'AdjNum',
        'od_claim_payments' => 'ClaimPaymentNum',
        'od_claim_procs' => 'ClaimProcNum',
        'claim_procs' => 'ClaimProcNum',
        'od_pay_splits' => 'SplitNum',
        'pay_splits' => 'SplitNum',
        'treatment_plans' => 'TreatPlanNum',
        'od_treatment_plan_attachments' => 'TreatPlanAttachNum',
        'od_payments' => 'PayNum',
        'od_deposits' => 'DepositNum',
        'od_recalls' => 'RecallNum',
        'od_recall_types' => 'RecallTypeNum',
        'od_schedule' => 'ScheduleNum',
        'od_definitions' => 'DefNum',
        'od_carriers' => 'CarrierNum',
        'od_insplans' => 'PlanNum',
        'od_pay_plan_charges' => 'PayPlanChargeNum',
        'od_procedures' => 'CodeNum',
        'od_clinics' => 'ClinicNum',
    ];

    /**
     * Tables that already had unique indexes added in previous migrations.
     *
     * @var array<string>
     */
    protected array $alreadyIndexed = [
        'od_patients',
        'od_providers',
        'od_patient_balances',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tableKeys as $tableName => $pk) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $pk)) {
                continue;
            }

            $hasOfficeId = Schema::hasColumn($tableName, 'office_id');

            // 1. Check and drop duplicate records before applying unique constraint
            if (Schema::hasColumn($tableName, 'id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($pk) {
                        $table->index($pk);
                    });
                } catch (Throwable $e) {
                    // Index may already exist
                }

                try {
                    if ($hasOfficeId) {
                        DB::statement("
                            DELETE t1 FROM `{$tableName}` t1
                            INNER JOIN `{$tableName}` t2
                            ON t1.`office_id` = t2.`office_id`
                            AND t1.`{$pk}` = t2.`{$pk}`
                            WHERE t1.`id` > t2.`id`
                              AND t1.`{$pk}` IS NOT NULL
                        ");
                    } else {
                        DB::statement("
                            DELETE t1 FROM `{$tableName}` t1
                            INNER JOIN `{$tableName}` t2
                            ON t1.`{$pk}` = t2.`{$pk}`
                            WHERE t1.`id` > t2.`id`
                              AND t1.`{$pk}` IS NOT NULL
                        ");
                    }
                } catch (Throwable $e) {
                    // Ignore error on environments (e.g. SQLite memory DBs) that don't support multi-table DELETE syntax
                }
            }

            // 2. Add composite unique index on [office_id, <primaryKey>] for tables not yet indexed
            if ($hasOfficeId && ! in_array($tableName, $this->alreadyIndexed, true)) {
                $indexName = "{$tableName}_office_".strtolower($pk).'_unique';
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($pk, $indexName) {
                        $table->unique(['office_id', $pk], $indexName);
                    });
                } catch (Throwable $e) {
                    // Index may already exist
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tableKeys as $tableName => $pk) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'office_id') || in_array($tableName, $this->alreadyIndexed, true)) {
                continue;
            }

            $indexName = "{$tableName}_office_".strtolower($pk).'_unique';

            try {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            } catch (Throwable $e) {
                // Index may not exist
            }
        }
    }
};
