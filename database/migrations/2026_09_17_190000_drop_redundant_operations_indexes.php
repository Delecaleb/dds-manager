<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops indexes that cost write time on every sync but can never be chosen by the planner,
 * because another index already starts with the same columns:
 *
 *   idx_proc_logs_ops_status_date       (office_id,ProcStatus,ProcDate,ClinicNum)
 *      → strict prefix of idx_proc_logs_ops_status_date_pat (… ,PatNum)
 *   idx_od_appts_ops_status_date        (office_id,AptStatus,AptDateTime,ClinicNum)
 *      → strict prefix of idx_od_appts_ops_status_date_pat (… ,PatNum)
 *   od_procedure_logs_office_id_index   (office_id)
 *      → prefix of every idx_proc_logs_ops_* composite
 *   od_procedure_logs_procstatus_index  (ProcStatus)
 *      → a handful of distinct values; never selective enough to use
 *
 * On od_procedure_logs the indexes had grown to 2.3x the size of the table data, which on
 * shared hosting costs disk quota as well as upsert throughput.
 *
 * Every drop is conditional, so this runs cleanly whether or not the environment created
 * these indexes in the first place.
 */
return new class extends Migration
{
    /** @var array<string, array<string, string>> table => [index name => columns to restore on rollback] */
    private array $redundant = [
        'od_procedure_logs' => [
            'idx_proc_logs_ops_status_date' => 'office_id, ProcStatus, ProcDate, ClinicNum',
            'od_procedure_logs_office_id_index' => 'office_id',
            'od_procedure_logs_procstatus_index' => 'ProcStatus',
        ],
        'od_appointments' => [
            'idx_od_appts_ops_status_date' => 'office_id, AptStatus, AptDateTime, ClinicNum',
            'od_appointments_office_id_index' => 'office_id',
        ],
    ];

    public function up(): void
    {
        if (! $this->isMysql()) {
            return; // SQLite (tests) does not carry these indexes.
        }

        foreach ($this->redundant as $table => $indexes) {
            foreach (array_keys($indexes) as $index) {
                if ($this->hasIndex($table, $index)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                }
            }
        }
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        foreach ($this->redundant as $table => $indexes) {
            foreach ($indexes as $index => $columns) {
                if (! $this->hasIndex($table, $index)) {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$columns})");
                }
            }
        }
    }

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function hasIndex(string $table, string $index): bool
    {
        return Schema::hasTable($table)
            && ! empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]));
    }
};
