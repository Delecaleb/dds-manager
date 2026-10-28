<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Covering indexes for the money sums every report runs (speed plan Phase 5.2).
 *
 * Each index leads with the filter every query has (office_id, business date), then the
 * columns reports filter / group on, then the DECIMAL amounts — so SUM(...) for a date
 * range is answered from the index without reading table rows. Possible only since the
 * amounts are DECIMAL (2026_10_27_110000).
 *
 * Each replaces a narrower "ops" index that is a prefix of it, so no query loses an index.
 * The server picks the method: InnoDB builds them online (reads and sync writes continue);
 * MyISAM tables (several od_* tables locally, 2026-10-27) are copied, which blocks writes to
 * that table while it runs — pause the sync queue.
 */
return new class extends Migration
{
    /** [table, new index, its columns, replaced index or null, replaced index columns or null] */
    private const INDEXES = [
        ['od_claim_procs', 'idx_claim_procs_cover_procdate',
            ['office_id', 'ProcDate', 'ClinicNum', 'ProvNum', 'Status', 'PlanNum', 'WriteOff', 'InsPayEst', 'InsEstTotal', 'WriteOffEst'],
            'idx_claim_procs_ops_procdate', ['office_id', 'ProcDate', 'ClinicNum']],
        ['od_claim_procs', 'idx_claim_procs_cover_datecp',
            ['office_id', 'DateCP', 'Status', 'ClinicNum', 'ProvNum', 'PlanNum', 'InsPayAmt'],
            'idx_claim_procs_ops_datecp', ['office_id', 'DateCP', 'Status', 'ClinicNum']],
        ['od_adjustments', 'idx_adjustments_cover_adjdate',
            ['office_id', 'AdjDate', 'ClinicNum', 'ProvNum', 'AdjType', 'PatNum', 'AdjAmt'],
            'idx_adjustments_ops_adjdate', ['office_id', 'AdjDate', 'ClinicNum']],
        ['od_pay_splits', 'idx_pay_splits_cover_datepay',
            ['office_id', 'DatePay', 'ClinicNum', 'ProvNum', 'PatNum', 'SplitAmt'],
            'idx_pay_splits_ops_datepay', ['office_id', 'DatePay', 'ClinicNum']],
        ['od_procedure_logs', 'idx_proc_logs_cover_status_date',
            ['office_id', 'ProcStatus', 'ProcDate', 'ClinicNum', 'ProvNum', 'PatNum', 'CodeNum', 'ProcFee'],
            'idx_proc_logs_ops_status_date_pat', ['office_id', 'ProcStatus', 'ProcDate', 'ClinicNum', 'PatNum']],
        ['od_payments', 'idx_payments_cover_paydate',
            ['office_id', 'PayDate', 'PayType', 'ClinicNum', 'PayAmt'],
            null, null],
        ['od_claim_payments', 'idx_claim_payments_cover_checkdate',
            ['office_id', 'CheckDate', 'ClinicNum', 'CheckAmt'],
            null, null],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as [$table, $name, $columns, $replaces, $oldColumns]) {
            if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
                continue;
            }

            $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
            $dropOld = $replaces !== null && Schema::hasIndex($table, $replaces);

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                $cols = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
                $drop = $dropOld ? ", DROP INDEX `{$replaces}`" : '';
                $this->withZeroDatesAllowed(fn () => DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$cols}){$drop}"));

                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($name, $columns, $dropOld, $replaces) {
                $t->index($columns, $name);
                if ($dropOld) {
                    $t->dropIndex($replaces);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::INDEXES) as [$table, $name, $columns, $replaces, $oldColumns]) {
            if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $name, $replaces, $oldColumns) {
                if ($replaces !== null && ! Schema::hasIndex($table, $replaces)) {
                    $t->index($oldColumns, $replaces);
                }
                $t->dropIndex($name);
            });
        }
    }

    /**
     * A table copy (MyISAM) re-validates every row, and synced OpenDental data has '0000-00-00'
     * in date columns, which strict mode rejects. Allow zero dates for this statement only.
     */
    private function withZeroDatesAllowed(Closure $statement): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
        DB::statement('SET SESSION sql_mode = ?', [implode(',', array_diff(explode(',', $previous), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']))]);

        try {
            $statement();
        } finally {
            DB::statement('SET SESSION sql_mode = ?', [$previous]);
        }
    }
};
