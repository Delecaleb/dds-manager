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
        'od_pay_splits' => 'SplitNum',
        'pay_splits' => 'SplitNum',
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
        'treatment_plans' => 'TreatPlanNum',
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

            // 1. Ensure office_id column exists
            if (! Schema::hasColumn($tableName, 'office_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('office_id')->default(1)->index();
                });
            }

            // 2. Remove duplicate records keeping the lowest id record
            if (Schema::hasColumn($tableName, 'id')) {
                DB::delete("
                    DELETE FROM `{$tableName}`
                    WHERE id IN (
                        SELECT id FROM (
                            SELECT t1.id
                            FROM `{$tableName}` t1
                            INNER JOIN `{$tableName}` t2
                            ON t1.`office_id` = t2.`office_id`
                            AND t1.`{$pk}` = t2.`{$pk}`
                            WHERE t1.`id` > t2.`id`
                              AND t1.`{$pk}` IS NOT NULL
                        ) as duplicate_rows
                    )
                ");
            }

            // 3. Check and apply unique index on [office_id, pk]
            $indexName = "{$tableName}_office_".strtolower($pk).'_unique';
            $existingIndexes = Schema::getIndexes($tableName);
            $hasUnique = false;

            foreach ($existingIndexes as $idx) {
                if ($idx['name'] === $indexName) {
                    $hasUnique = true;
                    break;
                }
                if (! empty($idx['unique']) && count($idx['columns']) === 2 && in_array('office_id', $idx['columns'], true) && in_array($pk, $idx['columns'], true)) {
                    $hasUnique = true;
                    break;
                }
            }

            if (! $hasUnique) {
                Schema::table($tableName, function (Blueprint $table) use ($pk, $indexName) {
                    $table->unique(['office_id', $pk], $indexName);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tableKeys as $tableName => $pk) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'office_id')) {
                continue;
            }

            $indexName = "{$tableName}_office_".strtolower($pk).'_unique';
            $existingIndexes = Schema::getIndexes($tableName);

            foreach ($existingIndexes as $idx) {
                if ($idx['name'] === $indexName) {
                    Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                        $table->dropUnique($indexName);
                    });
                    break;
                }
            }
        }
    }
};
