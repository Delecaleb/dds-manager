<?php

namespace App\Console\Commands;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * READ-ONLY validation for Phase 1 (Production).
 *
 * Unlike case acceptance, NET production intentionally CHANGES: the legacy formula used
 * abs() on adjustments/writeoffs; the confirmed definition (blueprint D3) uses signed
 * adjustments. This harness:
 *   1. proves GROSS matches an independent completed-only SUM(ProcFee)  → must be identical
 *   2. shows NET (new, signed) beside the old abs() net                 → the intended delta
 */
class BlueprintProductionCommand extends Command
{
    protected $signature = 'blueprint:production';

    protected $description = 'Read-only: validate ProductionService gross (parity) and net (intended change)';

    public function handle(ProductionService $production): int
    {
        $periods = [
            ['2025 FY', '2025-01-01', '2025-12-31'],
            ['2024 FY', '2024-01-01', '2024-12-31'],
            ['YTD',     now()->startOfYear()->toDateString(), now()->toDateString()],
        ];

        $rows = [];
        $grossOk = true;

        foreach ($periods as [$label, $start, $end]) {
            $filter = new MetricFilter($start, $end);
            $s = $production->summary($filter);

            // (1) Gross parity — independent completed-only sum
            $refGross = round((float) DB::table('od_procedure_logs')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->sum('ProcFee'), 2);
            $grossMatch = abs($refGross - $s->gross) < 0.01;
            $grossOk = $grossOk && $grossMatch;

            // (2) Old abs() net for comparison
            $oldNet = round($s->gross - abs($s->adjustments) - abs($s->writeOffs), 2);
            $delta = round($s->net - $oldNet, 2);

            $rows[] = [
                $label,
                number_format($s->gross, 2),
                number_format($s->adjustments, 2),
                number_format($s->writeOffs, 2),
                number_format($s->net, 2),
                $grossMatch ? '✓' : '✗',
                number_format($oldNet, 2),
                ($delta >= 0 ? '+' : '').number_format($delta, 2),
            ];
        }

        $this->info('Phase 1 — Production (read-only). Net uses signed adjustments (blueprint D3).');
        $this->table(
            ['Period', 'Gross', 'Adj (signed)', 'WriteOff', 'NET (new)', 'GrossOK', 'net (old abs)', 'Δ new−old'],
            $rows
        );

        if ($grossOk) {
            $this->info('GROSS: parity PASS (matches independent completed-only SUM(ProcFee)).');
        } else {
            $this->error('GROSS: parity FAIL — investigate before migrating call sites.');
        }
        $this->line('NET: the "Δ new−old" column is the INTENDED correction (signed vs abs adjustments).');
        $this->line('Old code double-penalised positive adjustments; new net reflects the confirmed rule.');

        return $grossOk ? self::SUCCESS : self::FAILURE;
    }
}
