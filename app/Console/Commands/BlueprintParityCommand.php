<?php

namespace App\Console\Commands;

use App\Domain\Support\MetricFilter;
use App\Domain\TreatmentAcceptance\TreatmentAcceptanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * READ-ONLY parity harness for the refactor (Phase 0 — Case Acceptance).
 *
 * Runs the legacy inline case-acceptance SQL (exactly as embedded in KpisController)
 * side-by-side with the new TreatmentAcceptanceService over a fixed "golden" input set,
 * and proves that the ONLY behavioural difference is the intentional D1 status-set change
 * (legacy counts completed as 'C' only; the service counts ['C','2']).
 *
 * Columns:
 *   Legacy    — reference SQL, completed = 'C'  only        (what the app does today)
 *   CanonRef  — reference SQL, completed = ['C','2']        (legacy logic + D1 status set)
 *   Service   — TreatmentAcceptanceService::rate()          (the new code)
 *   Match     — Service == CanonRef ?  (service reproduces legacy logic exactly)
 *   D1 Δ      — Service − Legacy       (the intentional change, attributable to D1)
 *
 * Nothing is written; no application code path is altered by running this.
 *
 * @see refractor-blueprint/06-migration-plan.md
 */
class BlueprintParityCommand extends Command
{
    protected $signature = 'blueprint:parity';

    protected $description = 'Read-only: verify TreatmentAcceptanceService parity with legacy case-acceptance SQL';

    public function handle(TreatmentAcceptanceService $service): int
    {
        $periods = [
            ['2025 FY', '2025-01-01', '2025-12-31'],
            ['2024 FY', '2024-01-01', '2024-12-31'],
            ['YTD',     now()->startOfYear()->toDateString(), now()->toDateString()],
            // 2010-2012 is the ONLY window containing ProcStatus='2' rows, so this period
            // is what actually exercises the D1 status-set change. (Those rows are fee-zero,
            // so D1 is a proven no-op for fee-weighted case acceptance — see PHASE-LOG.)
            ['2010-12', '2010-01-01', '2012-12-31'],
            ['Empty',   '2000-01-01', '2000-12-31'],
        ];

        $segments = [
            ['doctor',  false],
            ['hygiene', true],
            ['all',     null],
        ];

        $rows = [];
        $allMatch = true;

        foreach ($segments as [$segLabel, $hygiene]) {
            foreach ($periods as [$periodLabel, $start, $end]) {
                $filter = new MetricFilter($start, $end, [], [], $hygiene);

                $legacy = $this->referenceRate($start, $end, $hygiene, ['TP'], ['C']);
                $canonRef = $this->referenceRate($start, $end, $hygiene, ['TP', '1'], ['C', '2']);
                $svc = $service->rate($filter);

                $match = abs($canonRef - $svc) < 0.01;
                $allMatch = $allMatch && $match;

                $d1 = round($svc - $legacy, 2);

                $rows[] = [
                    $segLabel,
                    $periodLabel,
                    number_format($legacy, 2).'%',
                    number_format($canonRef, 2).'%',
                    number_format($svc, 2).'%',
                    $match ? '✓' : '✗ MISMATCH',
                    ($d1 == 0.0 ? '—' : ($d1 > 0 ? '+' : '').number_format($d1, 2)),
                ];
            }
        }

        $this->info('Phase 0 parity — Case Acceptance (read-only, no changes made)');
        $this->table(
            ['Segment', 'Period', "Legacy('C')", "CanonRef('C','2')", 'Service', 'Match', 'D1 Δ'],
            $rows
        );

        if ($allMatch) {
            $this->info('PASS: the service reproduces the legacy computation exactly under the canonical status set.');
            $this->line('Any non-zero "D1 Δ" is the intentional fix (legacy under-counted \'2\'-status completions).');

            return self::SUCCESS;
        }

        $this->error('FAIL: at least one Service value does not match the legacy logic under the canonical status set.');
        $this->line('Investigate before switching any call site — this is a real discrepancy, not a D1 change.');

        return self::FAILURE;
    }

    /**
     * The legacy inline case-acceptance calculation, parameterised by status set and hygiene
     * segment. Mirrors the SQL embedded in KpisController (doctor/hygiene caRates blocks).
     *
     * @param  string[]  $tpStatuses
     * @param  string[]  $completedStatuses
     */
    private function referenceRate(string $start, string $end, ?bool $hygiene, array $tpStatuses, array $completedStatuses): float
    {
        $tp = $this->inList($tpStatuses);
        $c = $this->inList($completedStatuses);

        $join = '';
        $where = 'pl.ProcDate BETWEEN ? AND ?';
        if ($hygiene !== null) {
            $join = 'JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum';
            $where = "pc.IsHygiene = '".($hygiene ? 'true' : 'false')."' AND ".$where;
        }

        $r = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN pl.ProcStatus IN ({$tp}) THEN pl.ProcFee ELSE 0 END), 0) AS proposed,
                COALESCE(SUM(CASE WHEN pl.ProcStatus IN ({$c})  THEN pl.ProcFee ELSE 0 END), 0) AS completed,
                COALESCE(SUM(CASE WHEN pl.ProcStatus IN ({$tp}) AND pl.AptNum IS NOT NULL AND pl.AptNum <> '0'
                             THEN pl.ProcFee ELSE 0 END), 0) AS accepted
            FROM od_procedure_logs pl
            {$join}
            WHERE {$where}
        ", [$start, $end]);

        $proposed = (float) ($r->proposed ?? 0);

        return $proposed > 0
            ? round(((float) $r->completed + (float) $r->accepted) / $proposed * 100, 2)
            : 0.0;
    }

    /** @param string[] $statuses */
    private function inList(array $statuses): string
    {
        return implode(', ', array_map(fn ($s) => "'".addslashes($s)."'", $statuses));
    }
}
