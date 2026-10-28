<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Money columns: VARCHAR(255) → DECIMAL(12,2) (speed plan Phase 5.1).
 *
 * Why: text money can't be summed from an index (every SUM fetched and converted each row,
 * ~15× slower than an index-only count) and summed as floating point (28355.789999999957).
 * DECIMAL is exact; a double would keep the drift.
 *
 * Checked 2026-10-27 on every column below (≈15M values): no NULL, blank or non-numeric
 * value, at most 2 decimals, largest |value| 999,999.99 — DECIMAL(12,2) holds all exactly.
 *
 * MySQL/MariaDB: one ALTER per table (Laravel would copy the table once per column).
 * The ALTER copies the table and blocks writes to it while it runs — on live, run in a
 * maintenance window with the sync queue paused.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private const COLUMNS = [
        'od_procedure_logs' => ['ProcFee', 'Discount', 'TaxAmt', 'DiscountPlanAmt'],
        'od_claim_procs' => [
            'FeeBilled', 'InsPayEst', 'DedApplied', 'InsPayAmt', 'WriteOff', 'CopayAmt', 'PaidOtherIns',
            'BaseEst', 'CopayOverride', 'DedEst', 'DedEstOverride', 'InsEstTotal', 'InsEstTotalOverride',
            'PaidOtherInsOverride', 'WriteOffEst', 'WriteOffEstOverride', 'AllowedOverride',
        ],
        'od_adjustments' => ['AdjAmt'],
        'od_pay_splits' => ['SplitAmt'],
        'od_claim_payments' => ['CheckAmt'],
        'od_payments' => ['PayAmt', 'MerchantFee'],
        'od_pay_plan_charges' => ['Principal', 'Interest'],
        'od_patients' => ['EstBalance', 'Bal_0_30', 'Bal_31_60', 'Bal_61_90', 'BalOver90', 'InsEst', 'BalTotal'],
    ];

    public function up(): void
    {
        $this->convert('decimal', 'DECIMAL(12,2) NULL', fn (Blueprint $t, string $c) => $t->decimal($c, 12, 2)->nullable()->change());
    }

    public function down(): void
    {
        $this->convert('varchar', 'VARCHAR(255) NULL', fn (Blueprint $t, string $c) => $t->string($c, 255)->nullable()->change());
    }

    private function convert(string $targetType, string $mysqlType, Closure $portable): void
    {
        $mysql = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // Skip columns already converted, so a re-run after a failure copies only what's left.
            $current = collect(Schema::getColumns($table))->pluck('type_name', 'name');
            $columns = array_values(array_filter($columns, fn ($c) => $current->has($c) && $current[$c] !== $targetType));
            if ($columns === []) {
                continue;
            }

            if ($mysql) {
                $modify = implode(', ', array_map(fn ($c) => "MODIFY `{$c}` {$mysqlType}", $columns));
                $this->withZeroDatesAllowed(fn () => DB::statement("ALTER TABLE `{$table}` {$modify}"));

                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($columns, $portable) {
                foreach ($columns as $c) {
                    $portable($t, $c);
                }
            });
        }
    }

    /**
     * The ALTER copies the whole table and re-validates every row, and synced OpenDental data
     * has '0000-00-00' in other date columns (e.g. od_patients.Birthdate), which strict mode
     * rejects. Allow zero dates for this statement only; everything else stays strict.
     */
    private function withZeroDatesAllowed(Closure $statement): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
        $relaxed = implode(',', array_diff(explode(',', $previous), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));

        DB::statement('SET SESSION sql_mode = ?', [$relaxed]);

        try {
            $statement();
        } finally {
            DB::statement('SET SESSION sql_mode = ?', [$previous]);
        }
    }
};
