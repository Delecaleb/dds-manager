<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OpenDental stores every key (PatNum, ProcNum, ClaimProcNum, DefNum, …) as
     * BIGINT. Offices using random primary keys produce values above INT's
     * 2,147,483,647, and a single such row makes the upsert fail with
     * "Numeric value out of range" — the sync can then never move past that
     * batch. Every INT/MEDIUMINT column mirrored from OpenDental is widened to
     * BIGINT, keeping nullability, default and indexes.
     *
     * Rebuilds each table once (one ALTER per table). Pause the sync cron while
     * this runs; large tables (claim procs, pay splits) take minutes.
     *
     * @var list<string>
     */
    private array $tables = [
        'od_adjustments',
        'od_appointments',
        'od_carriers',
        'od_claim_payments',
        'od_claim_procs',
        'od_clinics',
        'od_definitions',
        'od_deposits',
        'od_histappointments',
        'od_insplans',
        'od_patient_balances',
        'od_patients',
        'od_pay_plan_charges',
        'od_pay_splits',
        'od_payments',
        'od_procedure_logs',
        'od_procedures',
        'od_providers',
        'od_recall_types',
        'od_recalls',
        'od_schedules',
        'od_statements',
        'od_treatment_plan_attachments',
        'treatment_plans',
    ];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return; // SQLite (tests) has no fixed integer widths.
        }

        DB::statement("SET SESSION sql_mode = ''");

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                $this->widenTable($table);
            }
        }
    }

    public function down(): void
    {
        // Irreversible: narrowing back to INT would fail or corrupt large keys.
    }

    /**
     * Widen every INT/MEDIUMINT column of one table (except local id/office_id) in a single ALTER.
     */
    public function widenTable(string $table): int
    {
        $columns = DB::select(
            'SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND DATA_TYPE IN (\'int\', \'mediumint\')
                AND COLUMN_NAME NOT IN (\'id\', \'office_id\')
              ORDER BY ORDINAL_POSITION',
            [$table]
        );

        if ($columns === []) {
            return 0;
        }

        $modifications = array_map(function (object $column): string {
            $nullable = $column->IS_NULLABLE === 'YES';
            // MariaDB reports a NULL default as the string 'NULL'; MySQL as NULL.
            $default = $column->COLUMN_DEFAULT;
            $hasNullDefault = $default === null || strtoupper((string) $default) === 'NULL';

            $definition = '`'.str_replace('`', '', $column->COLUMN_NAME).'` BIGINT '.($nullable ? 'NULL' : 'NOT NULL');

            if (! $hasNullDefault && is_numeric(trim((string) $default, "'"))) {
                $definition .= ' DEFAULT '.(int) trim((string) $default, "'");
            } elseif ($hasNullDefault && $nullable) {
                $definition .= ' DEFAULT NULL';
            }

            return 'MODIFY '.$definition;
        }, $columns);

        DB::statement('ALTER TABLE `'.$table.'` '.implode(', ', $modifications));

        return count($columns);
    }
};
