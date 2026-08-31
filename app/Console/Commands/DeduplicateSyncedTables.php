<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeduplicateSyncedTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:deduplicate
                            {table? : Specific table to deduplicate (e.g. od_patients, od_providers, od_appointments), or "all"}
                            {--dry-run : Check and report duplicates without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remove duplicate records per table based on office_id and OpenDental primary key.';

    /**
     * Map of table names to their OpenDental primary key column.
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
        'od_statements' => 'StatementNum',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetTable = $this->argument('table');
        $dryRun = (bool) $this->option('dry-run');

        $tablesToProcess = [];

        if (empty($targetTable) || strtolower($targetTable) === 'all') {
            $tablesToProcess = $this->tableKeys;
        } else {
            if (! isset($this->tableKeys[$targetTable])) {
                $this->error("Unknown or unsynced table '{$targetTable}'.");
                $this->info('Available tables: '.implode(', ', array_keys($this->tableKeys)));

                return Command::FAILURE;
            }
            $tablesToProcess = [$targetTable => $this->tableKeys[$targetTable]];
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '').'Starting deduplication scan across '.count($tablesToProcess).' table(s)...');
        $this->newLine();

        $totalRemoved = 0;

        foreach ($tablesToProcess as $tableName => $pk) {
            if (! Schema::hasTable($tableName)) {
                $this->line("  [SKIP] Table '{$tableName}' does not exist in local database.");

                continue;
            }

            if (! Schema::hasColumn($tableName, $pk)) {
                $this->line("  [SKIP] Table '{$tableName}' has no column '{$pk}'.");

                continue;
            }

            $hasOfficeId = Schema::hasColumn($tableName, 'office_id');
            $hasId = Schema::hasColumn($tableName, 'id');

            if (! $hasId) {
                $this->line("  [SKIP] Table '{$tableName}' has no auto-increment 'id' column for row deduplication.");

                continue;
            }

            // Count duplicate records
            $duplicateCount = $this->countDuplicates($tableName, $pk, $hasOfficeId);

            if ($duplicateCount === 0) {
                $this->line("  [OK] Table '{$tableName}': No duplicate records found.");

                continue;
            }

            if ($dryRun) {
                $this->warn("  [DRY-RUN] Table '{$tableName}': Found {$duplicateCount} duplicate record(s).");
                $totalRemoved += $duplicateCount;

                continue;
            }

            // Perform deletion
            $deleted = $this->deleteDuplicates($tableName, $pk, $hasOfficeId);
            $this->info("  [CLEANED] Table '{$tableName}': Successfully removed {$deleted} duplicate record(s).");
            $totalRemoved += $deleted;
        }

        $this->newLine();
        $action = $dryRun ? 'Found' : 'Removed';
        $this->info("Deduplication complete. Total {$action}: {$totalRemoved} duplicate row(s).");

        return Command::SUCCESS;
    }

    /**
     * Count how many extra duplicate rows exist in a table.
     */
    protected function countDuplicates(string $tableName, string $pk, bool $hasOfficeId): int
    {
        $groupCols = $hasOfficeId ? "office_id, `{$pk}`" : "`{$pk}`";

        $results = DB::select("
            SELECT {$groupCols}, (COUNT(*) - 1) as extra_rows
            FROM `{$tableName}`
            WHERE `{$pk}` IS NOT NULL AND `{$pk}` != ''
            GROUP BY {$groupCols}
            HAVING COUNT(*) > 1
        ");

        $total = 0;
        foreach ($results as $row) {
            $total += (int) $row->extra_rows;
        }

        return $total;
    }

    /**
     * Delete duplicate rows retaining the record with the lowest 'id'.
     */
    protected function deleteDuplicates(string $tableName, string $pk, bool $hasOfficeId): int
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            if ($hasOfficeId) {
                return DB::delete("
                    DELETE FROM `{$tableName}`
                    WHERE id IN (
                        SELECT t1.id
                        FROM `{$tableName}` t1
                        INNER JOIN `{$tableName}` t2
                        ON t1.`office_id` = t2.`office_id`
                        AND t1.`{$pk}` = t2.`{$pk}`
                        WHERE t1.`id` > t2.`id`
                          AND t1.`{$pk}` IS NOT NULL
                    )
                ");
            }

            return DB::delete("
                DELETE FROM `{$tableName}`
                WHERE id IN (
                    SELECT t1.id
                    FROM `{$tableName}` t1
                    INNER JOIN `{$tableName}` t2
                    ON t1.`{$pk}` = t2.`{$pk}`
                    WHERE t1.`id` > t2.`id`
                      AND t1.`{$pk}` IS NOT NULL
                )
            ");
        }

        if ($hasOfficeId) {
            return DB::delete("
                DELETE t1 FROM `{$tableName}` t1
                INNER JOIN `{$tableName}` t2
                ON t1.`office_id` = t2.`office_id`
                AND t1.`{$pk}` = t2.`{$pk}`
                WHERE t1.`id` > t2.`id`
                  AND t1.`{$pk}` IS NOT NULL
            ");
        }

        return DB::delete("
            DELETE t1 FROM `{$tableName}` t1
            INNER JOIN `{$tableName}` t2
            ON t1.`{$pk}` = t2.`{$pk}`
            WHERE t1.`id` > t2.`id`
              AND t1.`{$pk}` IS NOT NULL
        ");
    }
}
