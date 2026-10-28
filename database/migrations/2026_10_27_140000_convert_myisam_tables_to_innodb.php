<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convert every MyISAM table in this database to InnoDB (decision 2026-10-27).
 *
 * Locally 20 tables (od_claim_procs, od_pay_splits, od_payments, od_recalls, offices, …)
 * were MyISAM — most likely from the 2026-09 restore. MyISAM caches indexes in
 * key_buffer_size (16 MB here, against 1.66 GB of MyISAM indexes: 22% of index reads
 * missed), has no transactions, and is not crash-safe. InnoDB uses the 2 GB buffer pool
 * and is what tables created by migrations (and RDS) use, so this also makes local
 * timings representative of live. Tables already on InnoDB are untouched, so on a
 * server without MyISAM tables this does nothing.
 *
 * Each ALTER copies the table and blocks writes to it while it runs: pause the sync queue.
 * Not reversible (going back to MyISAM is never wanted).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $tables = DB::select(
            "SELECT TABLE_NAME AS name FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'MyISAM' AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY DATA_LENGTH + INDEX_LENGTH"
        );

        foreach ($tables as $table) {
            $this->withZeroDatesAllowed(fn () => DB::statement("ALTER TABLE `{$table->name}` ENGINE=InnoDB"));
        }
    }

    public function down(): void
    {
        // Intentionally irreversible.
    }

    /**
     * The table copy re-validates every row, and synced OpenDental data has '0000-00-00' in
     * date columns, which strict mode rejects. Allow zero dates for this statement only.
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
