<?php

namespace App\Services\Sync\Explorer;

use App\Models\Office;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\OpenDentalTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Side-by-side comparison of OpenDental (live) and the local synced copy.
 *
 * Safety rule: a local row is only reported as an orphan ("deleted in
 * OpenDental") when OpenDental successfully answered a primary-key lookup
 * without it. Any API failure, or a result that looks like the wrong database
 * (every row missing), yields "unverified" instead — which cannot be pruned.
 */
class ReconciliationService
{
    /** Below this many rows, "all of them are missing" is plausible and not treated as suspicious. */
    private const SUSPICIOUS_ORPHAN_MINIMUM = 10;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly OpenDentalSqlBuilder $sql,
        private readonly ExplorerQueryService $explorer,
    ) {}

    /**
     * @param  list<array{column: string, operator: string, value: string, logical: string}>  $conditions  already normalized (incl. date range)
     * @return array<string, mixed>
     */
    public function compare(OpenDentalTable $table, Office $office, array $conditions, int $limit): array
    {
        $startedAt = microtime(true);
        $officeId = (int) $office->id;
        $pk = $table->primaryKey;
        $orderColumn = $table->dateColumn ?? $pk;
        $limit = $this->explorer->clampLimit($limit);
        $warning = null;

        // 1. Live OpenDental slice.
        $liveRowsByPk = [];
        $liveError = null;

        try {
            $live = $this->explorer->live($table, $office, ['*'], $conditions, $orderColumn, 'asc', $limit);

            foreach ($live['rows'] as $row) {
                if (isset($row[$pk])) {
                    $liveRowsByPk[(string) $row[$pk]] = $row;
                }
            }
        } catch (Throwable $e) {
            $liveError = $e->getMessage();
        }

        // 2. Local slice for this office, same filters and ordering.
        $localQuery = $this->explorer->localBuilder($table, $officeId, $conditions)->orderBy($orderColumn);

        if ($orderColumn !== $pk) {
            $localQuery->orderBy($pk);
        }

        $localRowsByPk = [];

        foreach ($localQuery->limit($limit)->get() as $row) {
            $row = (array) $row;

            if (isset($row[$pk])) {
                $localRowsByPk[(string) $row[$pk]] = $row;
            }
        }

        $unverifiedKeys = [];
        $orphanKeys = [];
        $missingKeys = [];

        if ($liveError !== null) {
            // Without a live answer nothing can be classified as deleted or missing.
            $unverifiedKeys = array_map('strval', array_keys($localRowsByPk));
        } else {
            // 3a. Local rows absent from the live slice may just be outside its LIMIT/order:
            //     confirm by primary key before calling them orphans.
            $potentialOrphans = array_map('strval', array_keys(array_diff_key($localRowsByPk, $liveRowsByPk)));

            foreach (array_chunk($potentialOrphans, 500) as $chunk) {
                try {
                    $foundRows = $this->queryService->forOffice($office)
                        ->shortQuery($this->sql->selectByKeys($table->key, $pk, $chunk));
                } catch (Throwable $e) {
                    $unverifiedKeys = array_merge($unverifiedKeys, $chunk);
                    $liveError ??= $e->getMessage();

                    continue;
                }

                $found = [];

                foreach ((array) $foundRows as $foundRow) {
                    $foundRow = (array) $foundRow;

                    if (isset($foundRow[$pk])) {
                        $found[(string) $foundRow[$pk]] = true;
                        $liveRowsByPk[(string) $foundRow[$pk]] = $foundRow;
                    }
                }

                foreach ($chunk as $key) {
                    if (! isset($found[$key])) {
                        $orphanKeys[] = $key;
                    }
                }
            }

            // Every local row "deleted" usually means the API answered from the wrong
            // database or credentials, not a mass deletion. Refuse to call them orphans.
            if (count($orphanKeys) >= self::SUSPICIOUS_ORPHAN_MINIMUM && count($orphanKeys) === count($localRowsByPk)) {
                $unverifiedKeys = array_merge($unverifiedKeys, $orphanKeys);
                $orphanKeys = [];
                $warning = 'OpenDental returned none of the local records. Check this office\'s API connection before pruning anything.';
            }

            // 3b. Live rows absent from the local slice may be beyond the local LIMIT:
            //     confirm against the full local table for this office.
            $potentialMissing = array_map('strval', array_keys(array_diff_key($liveRowsByPk, $localRowsByPk)));

            foreach (array_chunk($potentialMissing, 500) as $chunk) {
                $foundLocal = DB::table($table->localTable)
                    ->where('office_id', $officeId)
                    ->whereIn($pk, $chunk)
                    ->get();

                foreach ($foundLocal as $row) {
                    $localRowsByPk[(string) $row->{$pk}] = (array) $row;
                }

                foreach ($chunk as $key) {
                    if (! isset($localRowsByPk[$key])) {
                        $missingKeys[] = $key;
                    }
                }
            }
        }

        // 4. Classify rows present on both sides.
        $orphanSet = array_flip($orphanKeys);
        $unverifiedSet = array_flip($unverifiedKeys);
        $compareColumns = $table->compareColumns !== []
            ? $table->compareColumns
            : array_values(array_diff($this->explorer->columns($table), ['id', 'created_at', 'updated_at', 'office_id']));
        $relationalContext = $this->relationalContext($table->localTable, $officeId, $localRowsByPk);

        $diffRows = [];
        $matchedKeys = [];
        $discrepancyKeys = [];

        foreach ($localRowsByPk as $key => $localRow) {
            $key = (string) $key;

            if (isset($orphanSet[$key]) || isset($unverifiedSet[$key]) || ! isset($liveRowsByPk[$key])) {
                continue;
            }

            $fieldDiffs = $this->fieldDiffs($localRow, $liveRowsByPk[$key], $compareColumns);
            $isDiscrepancy = $fieldDiffs !== [];

            if ($isDiscrepancy) {
                $discrepancyKeys[] = $key;
            } else {
                $matchedKeys[] = $key;
            }

            $diffRows[] = $this->row(
                $isDiscrepancy ? 'discrepancy' : 'matched',
                $key,
                $officeId,
                $pk,
                'both',
                $isDiscrepancy ? $liveRowsByPk[$key] : ($liveRowsByPk[$key] ?: $localRow),
                $fieldDiffs,
                $this->relationalIssue($table->localTable, $localRow, $relationalContext),
                $isDiscrepancy ? $localRow : null,
            );
        }

        foreach ($orphanKeys as $key) {
            $diffRows[] = $this->row('orphan', $key, $officeId, $pk, 'local_only', $localRowsByPk[$key] ?? [], [],
                $this->relationalIssue($table->localTable, $localRowsByPk[$key] ?? [], $relationalContext));
        }

        foreach ($unverifiedKeys as $key) {
            $diffRows[] = $this->row('unverified', $key, $officeId, $pk, 'local_only', $localRowsByPk[$key] ?? []);
        }

        foreach ($missingKeys as $key) {
            $diffRows[] = $this->row('missing', $key, $officeId, $pk, 'live_only', $liveRowsByPk[$key] ?? null);
        }

        $total = count($diffRows);

        return [
            'success' => true,
            'table' => $table->key,
            'local_table' => $table->localTable,
            'office_id' => $officeId,
            'primary_key' => $pk,
            'date_column' => $table->dateColumn,
            'execution_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'live_error' => $liveError,
            'warning' => $warning,
            'repairable' => $table->isRepairable(),
            'columns' => $this->explorer->columns($table),
            'summary' => [
                'live_count' => count($liveRowsByPk),
                'local_count' => count($localRowsByPk),
                'matched_count' => count($matchedKeys),
                'discrepancy_count' => count($discrepancyKeys),
                'orphan_count' => count($orphanKeys),
                'missing_count' => count($missingKeys),
                'unverified_count' => count($unverifiedKeys),
                'match_rate_pct' => $total > 0 ? round(count($matchedKeys) / $total * 100, 1) : 100,
            ],
            'orphan_keys' => array_values($orphanKeys),
            'missing_keys' => array_values($missingKeys),
            'discrepancy_keys' => $discrepancyKeys,
            'unverified_keys' => array_values($unverifiedKeys),
            'diff_rows' => $diffRows,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, array{local: mixed, live: mixed}>  $fieldDiffs
     * @param  array<string, mixed>|null  $localData
     * @return array<string, mixed>
     */
    private function row(string $status, string $key, int $officeId, string $pk, string $source, ?array $data, array $fieldDiffs = [], ?string $relationalIssue = null, ?array $localData = null): array
    {
        [$label, $badge] = match ($status) {
            'discrepancy' => ['Modified in OpenDental (Data Discrepancy)', 'blue'],
            'matched' => ['Synced & Matched', 'emerald'],
            'orphan' => ['Deleted in OpenDental (Orphan in Local DB)', 'red'],
            'unverified' => ['Could not verify against OpenDental', 'slate'],
            'missing' => ['Missing from Local DB (Sync Needed)', 'amber'],
        };

        return array_filter([
            'status' => $status,
            'status_label' => $label,
            'status_badge' => $badge,
            'pk' => $key,
            'office_id' => $officeId,
            'primary_key_name' => $pk,
            'source' => $source,
            'field_diffs' => $fieldDiffs,
            'relational_issue' => $relationalIssue,
            'data' => $data,
            'local_data' => $localData,
        ], fn ($value, $field) => $field !== 'local_data' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param  array<string, mixed>  $localRow
     * @param  array<string, mixed>  $liveRow
     * @param  list<string>  $columns
     * @return array<string, array{local: mixed, live: mixed}>
     */
    private function fieldDiffs(array $localRow, array $liveRow, array $columns): array
    {
        $diffs = [];

        foreach ($columns as $column) {
            if (! array_key_exists($column, $localRow) || ! array_key_exists($column, $liveRow)) {
                continue;
            }

            $local = $localRow[$column];
            $live = $liveRow[$column];

            if (is_numeric($local) && is_numeric($live)) {
                $differs = abs((float) $local - (float) $live) > 0.005;
            } elseif (is_string($local) && is_string($live) && preg_match('/date|time/i', $column)) {
                $differs = substr(str_replace('T', ' ', trim($local)), 0, 10) !== substr(str_replace('T', ' ', trim($live)), 0, 10);
            } else {
                $differs = (string) $local !== (string) $live;
            }

            if ($differs) {
                $diffs[$column] = ['local' => $local, 'live' => $live];
            }
        }

        return $diffs;
    }

    /**
     * Bulk-load parent/child data once so integrity checks are not N+1.
     *
     * @param  array<string, array<string, mixed>>  $localRowsByPk
     * @return array<string, mixed>
     */
    private function relationalContext(string $localTable, int $officeId, array $localRowsByPk): array
    {
        $ids = fn (string $column) => array_values(array_unique(array_filter(
            array_map(fn (array $row) => (int) ($row[$column] ?? 0), $localRowsByPk)
        )));

        $context = [];

        if ($localTable === 'od_pay_splits' && ($payNums = $ids('PayNum')) !== [] && Schema::hasTable('od_payments')) {
            $context['parent_payments'] = DB::table('od_payments')->where('office_id', $officeId)->whereIn('PayNum', $payNums)->pluck('PayAmt', 'PayNum')->all();
            $context['split_sums'] = DB::table('od_pay_splits')->where('office_id', $officeId)->whereIn('PayNum', $payNums)
                ->groupBy('PayNum')->select('PayNum', DB::raw('SUM(SplitAmt) as split_sum'))->pluck('split_sum', 'PayNum')->all();
        }

        if ($localTable === 'od_payments' && ($payNums = $ids('PayNum')) !== []) {
            $context['split_stats'] = DB::table('od_pay_splits')->where('office_id', $officeId)->whereIn('PayNum', $payNums)
                ->groupBy('PayNum')->select('PayNum', DB::raw('COUNT(*) as split_cnt'), DB::raw('SUM(SplitAmt) as split_sum'))
                ->get()->mapWithKeys(fn ($s) => [$s->PayNum => ['count' => (int) $s->split_cnt, 'sum' => (float) $s->split_sum]])->all();
        }

        if ($localTable === 'od_claim_procs') {
            if (($claimPaymentNums = $ids('ClaimPaymentNum')) !== [] && Schema::hasTable('od_claim_payments')) {
                $context['existing_claim_payments'] = array_flip(DB::table('od_claim_payments')->where('office_id', $officeId)
                    ->whereIn('ClaimPaymentNum', $claimPaymentNums)->pluck('ClaimPaymentNum')->all());
            }

            if (($claimNums = $ids('ClaimNum')) !== [] && Schema::hasTable('od_claims')) {
                $context['existing_claims'] = array_flip(DB::table('od_claims')->where('office_id', $officeId)
                    ->whereIn('ClaimNum', $claimNums)->pluck('ClaimNum')->all());
            }
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $context
     */
    private function relationalIssue(string $localTable, array $row, array $context): ?string
    {
        if ($localTable === 'od_pay_splits' && isset($context['parent_payments']) && ($payNum = (int) ($row['PayNum'] ?? 0)) > 0) {
            if (! array_key_exists($payNum, $context['parent_payments'])) {
                return "Parent Payment #{$payNum} is missing in local DB (Relational Orphan)";
            }

            $payAmt = (float) $context['parent_payments'][$payNum];
            $splitSum = (float) ($context['split_sums'][$payNum] ?? 0);

            return abs($splitSum - $payAmt) > 0.01
                ? "Split allocation mismatch: Local splits sum to \${$splitSum} vs Payment #{$payNum} amount \${$payAmt}"
                : null;
        }

        if ($localTable === 'od_payments' && isset($context['split_stats']) && ($payNum = (int) ($row['PayNum'] ?? 0)) > 0) {
            $stats = $context['split_stats'][$payNum] ?? null;

            if ($stats === null || $stats['count'] === 0) {
                return "No child pay splits found in local DB for Payment #{$payNum}";
            }

            $payAmt = (float) ($row['PayAmt'] ?? 0);

            return abs($stats['sum'] - $payAmt) > 0.01
                ? "Child splits sum (\${$stats['sum']}) differs from PayAmt (\${$payAmt})"
                : null;
        }

        if ($localTable === 'od_claim_procs') {
            $claimPaymentNum = (int) ($row['ClaimPaymentNum'] ?? 0);

            if ($claimPaymentNum > 0 && isset($context['existing_claim_payments']) && ! isset($context['existing_claim_payments'][$claimPaymentNum])) {
                return "Parent Claim Payment #{$claimPaymentNum} missing locally";
            }

            $claimNum = (int) ($row['ClaimNum'] ?? 0);

            if ($claimNum > 0 && isset($context['existing_claims']) && ! isset($context['existing_claims'][$claimNum])) {
                return "Parent Claim #{$claimNum} missing locally";
            }
        }

        return null;
    }
}
