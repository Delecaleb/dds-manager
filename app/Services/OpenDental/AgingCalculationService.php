<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Computes real, transaction-level AR aging (Current / 30 / 60 / 90 / 120 /
 * 180 / 240 / 365+) per guarantor, replacing OpenDental's own coarse
 * 4-bucket patient balance snapshot.
 *
 * Method: per-procedure netting. Every completed procedure (ProcStatus='C')
 * is netted against the patient payments, insurance payments/write-offs, and
 * adjustments linked to that same ProcNum; the remainder is aged by the
 * procedure's own ProcDate. Adjustments/payments that aren't tied to a
 * specific procedure (family-level account entries) are aged by their own
 * transaction date instead. This is the standard "sub-ledger" aging method
 * used by most practice-management systems, and was chosen over a
 * chronological FIFO walk because OpenDental links nearly every payment/
 * adjustment/insurance line back to a ProcNum already.
 *
 * Bucket boundaries (Current=0-30, then 31-60, 61-90, 91-120, 121-180,
 * 181-240, 241-365, 365+) are the standard aging ladder implied by the
 * column labels; revisit if a side-by-side comparison against Jarvis's
 * real output shows different cutoffs.
 *
 * "Office" has no clinic-lookup table synced locally, and this deployment
 * only has one physical office, so it's a fixed label matching the location
 * name already hardcoded elsewhere in the app (e.g. resources/views/eod).
 */
class AgingCalculationService
{
    private const OFFICE_NAME = '8 Mile';

    /**
     * Allowlist mapping a DataTables column data-key to the outer-query
     * alias it may be ordered by. Every value here is a column selected in
     * pagedRows()'s outer SELECT (in BOTH the guarantor and patient
     * branches), so MySQL can ORDER BY the alias directly. Any requested
     * key not present here falls back to 'total' — this is the only thing
     * that ever reaches the ORDER BY clause, so a caller can never inject
     * arbitrary SQL through the sort parameter.
     *
     * Deliberately excluded (not meaningfully sortable):
     *   office        — a single constant label ('8 Mile')
     *   family_names  — GROUP_CONCAT list attached AFTER pagination
     *   family_ids    — same; no pre-LIMIT sort key exists
     */
    private const SORTABLE = [
        'guarantor_name' => 'guarantor_name',
        'guarantor_id' => 'guarantor_id',
        'bal_current' => 'bal_current',
        'bal_30' => 'bal_30',
        'bal_60' => 'bal_60',
        'bal_90' => 'bal_90',
        'bal_120' => 'bal_120',
        'bal_180' => 'bal_180',
        'bal_240' => 'bal_240',
        'bal_365' => 'bal_365',
        'credit_balance' => 'credit_balance',
        'contract' => 'contract',
        'total' => 'total',
    ];

    public function guarantorAging(
        string $asOfDate,
        ?string $search,
        bool $includeCredits,
        int $start,
        int $length,
        ?string $sortKey = null,
        string $sortDir = 'desc'
    ): array {
        $totalRecords = $this->countRecords($asOfDate, null, true, 'guarantor');
        $filteredRecords = $this->countRecords($asOfDate, $search, $includeCredits, 'guarantor');

        $rows = $this->pagedRows($asOfDate, $search, $includeCredits, $start, $length, 'guarantor', $sortKey, $sortDir);
        $rows = $this->attachFamilies($rows);

        $totals = $this->totals($asOfDate, $search, $includeCredits, 'guarantor');

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'data' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Same ledger engine as guarantorAging(), grouped per individual
     * patient instead of rolled up to their guarantor. Needed because
     * OpenDental's own per-patient Bal_0_30/.../BalTotal fields on
     * od_patients are unpopulated in this environment, so the old
     * by-patient query (reading those fields directly) always returned
     * zero rows.
     */
    public function patientAging(
        string $asOfDate,
        ?string $search,
        bool $includeCredits,
        int $start,
        int $length,
        ?string $sortKey = null,
        string $sortDir = 'desc'
    ): array {
        $totalRecords = $this->countRecords($asOfDate, null, true, 'patient');
        $filteredRecords = $this->countRecords($asOfDate, $search, $includeCredits, 'patient');

        $rows = $this->pagedRows($asOfDate, $search, $includeCredits, $start, $length, 'patient', $sortKey, $sortDir);

        $totals = $this->totals($asOfDate, $search, $includeCredits, 'patient');

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'data' => $rows,
            'totals' => $totals,
        ];
    }

    private function countRecords(string $asOfDate, ?string $search, bool $includeCredits, string $groupBy): int
    {
        $joinAlias = $groupBy === 'patient' ? 'p' : 'g';

        $sql = 'SELECT COUNT(*) as cnt FROM (' . $this->baseSql($includeCredits, $groupBy) . ') base
                JOIN od_patients ' . $joinAlias . ' ON ' . $joinAlias . '.PatNum = base.row_id
                WHERE 1=1 ' . $this->searchClause($search, $joinAlias);

        $bindings = array_merge($this->dateBindings($asOfDate), $this->searchBindings($search));

        return (int) DB::selectOne($sql, $bindings)->cnt;
    }

    private function pagedRows(string $asOfDate, ?string $search, bool $includeCredits, int $start, int $length, string $groupBy, ?string $sortKey = null, string $sortDir = 'desc'): Collection
    {
        // Resolve the requested sort against the allowlist (never the raw
        // request), then append a unique tiebreak so pagination is stable
        // across pages when the primary key has ties.
        $sortExpr = self::SORTABLE[$sortKey] ?? 'total';
        $dir = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';
        $tieBreak = $groupBy === 'patient' ? 'patient_id' : 'guarantor_id';
        $orderBy = "ORDER BY {$sortExpr} {$dir}, {$tieBreak} ASC";

        if ($groupBy === 'patient') {
            $sql = 'SELECT
                        base.guarantor_id,
                        base.row_id AS patient_id,
                        CONCAT(g.LName, \', \', g.FName) AS guarantor_name,
                        CONCAT(p.LName, \', \', p.FName) AS family_names,
                        p.PatNum AS family_ids,
                        base.bal_current, base.bal_30, base.bal_60, base.bal_90,
                        base.bal_120, base.bal_180, base.bal_240, base.bal_365,
                        base.total,
                        COALESCE(contract_agg.contract, 0) AS contract,
                        CASE WHEN base.total < 0 THEN -base.total ELSE 0 END AS credit_balance
                    FROM (' . $this->baseSql($includeCredits, 'patient') . ') base
                    JOIN od_patients p ON p.PatNum = base.row_id
                    JOIN od_patients g ON g.PatNum = base.guarantor_id
                    LEFT JOIN (' . $this->contractSql() . ') contract_agg ON contract_agg.guarantor_id = base.guarantor_id
                    WHERE 1=1 ' . $this->searchClause($search, 'p') . '
                    ' . $orderBy . '
                    LIMIT ? OFFSET ?';

            $bindings = array_merge($this->dateBindings($asOfDate), $this->searchBindings($search), [$length, $start]);

            $rows = collect(DB::select($sql, $bindings));

            return $rows->map(function ($row) {
                $row->office = self::OFFICE_NAME;

                return $row;
            });
        }

        $sql = 'SELECT
                    base.row_id AS guarantor_id,
                    CONCAT(g.LName, \', \', g.FName) AS guarantor_name,
                    base.bal_current, base.bal_30, base.bal_60, base.bal_90,
                    base.bal_120, base.bal_180, base.bal_240, base.bal_365,
                    base.total,
                    COALESCE(contract_agg.contract, 0) AS contract,
                    CASE WHEN base.total < 0 THEN -base.total ELSE 0 END AS credit_balance
                FROM (' . $this->baseSql($includeCredits, 'guarantor') . ') base
                JOIN od_patients g ON g.PatNum = base.row_id
                LEFT JOIN (' . $this->contractSql() . ') contract_agg ON contract_agg.guarantor_id = base.row_id
                WHERE 1=1 ' . $this->searchClause($search, 'g') . '
                ' . $orderBy . '
                LIMIT ? OFFSET ?';

        $bindings = array_merge($this->dateBindings($asOfDate), $this->searchBindings($search), [$length, $start]);

        return collect(DB::select($sql, $bindings));
    }

    public function totals(string $asOfDate, ?string $search, bool $includeCredits, string $groupBy): array
    {
        if ($groupBy === 'patient') {
            // Contract is a per-GUARANTOR balance, not per-patient. Summing
            // it once per patient row would multiply-count a guarantor's
            // contract balance across each of their dependents, so it's
            // totalled separately over the DISTINCT guarantor ids present
            // in the filtered patient set rather than joined per-row.
            $innerBase = $this->baseSql($includeCredits, 'patient');

            $sql = 'SELECT
                        COALESCE(SUM(base.bal_current), 0) AS current_total,
                        COALESCE(SUM(base.bal_30), 0) AS thirty_total,
                        COALESCE(SUM(base.bal_60), 0) AS sixty_total,
                        COALESCE(SUM(base.bal_90), 0) AS ninety_total,
                        COALESCE(SUM(base.bal_120), 0) AS onetwenty_total,
                        COALESCE(SUM(base.bal_180), 0) AS oneeighty_total,
                        COALESCE(SUM(base.bal_240), 0) AS twofourty_total,
                        COALESCE(SUM(base.bal_365), 0) AS threesixfive_total,
                        COALESCE(SUM(CASE WHEN base.total < 0 THEN -base.total ELSE 0 END), 0) AS credit_total,
                        (
                            SELECT COALESCE(SUM(contract_agg.contract), 0)
                            FROM (
                                SELECT DISTINCT base2.guarantor_id
                                FROM (' . $innerBase . ') base2
                                JOIN od_patients p2 ON p2.PatNum = base2.row_id
                                WHERE 1=1 ' . $this->searchClause($search, 'p2') . '
                            ) g_ids
                            LEFT JOIN (' . $this->contractSql() . ') contract_agg ON contract_agg.guarantor_id = g_ids.guarantor_id
                        ) AS contract_total,
                        COALESCE(SUM(base.total), 0) AS grand_total
                    FROM (' . $innerBase . ') base
                    JOIN od_patients p ON p.PatNum = base.row_id
                    WHERE 1=1 ' . $this->searchClause($search, 'p');

            $bindings = array_merge(
                $this->dateBindings($asOfDate),
                $this->searchBindings($search),
                $this->dateBindings($asOfDate),
                $this->searchBindings($search)
            );

            return (array) DB::selectOne($sql, $bindings);
        }

        $sql = 'SELECT
                    COALESCE(SUM(base.bal_current), 0) AS current_total,
                    COALESCE(SUM(base.bal_30), 0) AS thirty_total,
                    COALESCE(SUM(base.bal_60), 0) AS sixty_total,
                    COALESCE(SUM(base.bal_90), 0) AS ninety_total,
                    COALESCE(SUM(base.bal_120), 0) AS onetwenty_total,
                    COALESCE(SUM(base.bal_180), 0) AS oneeighty_total,
                    COALESCE(SUM(base.bal_240), 0) AS twofourty_total,
                    COALESCE(SUM(base.bal_365), 0) AS threesixfive_total,
                    COALESCE(SUM(CASE WHEN base.total < 0 THEN -base.total ELSE 0 END), 0) AS credit_total,
                    COALESCE(SUM(contract_agg.contract), 0) AS contract_total,
                    COALESCE(SUM(base.total), 0) AS grand_total
                FROM (' . $this->baseSql($includeCredits, 'guarantor') . ') base
                JOIN od_patients g ON g.PatNum = base.row_id
                LEFT JOIN (' . $this->contractSql() . ') contract_agg ON contract_agg.guarantor_id = base.row_id
                WHERE 1=1 ' . $this->searchClause($search, 'g');

        $bindings = array_merge($this->dateBindings($asOfDate), $this->searchBindings($search));

        return (array) DB::selectOne($sql, $bindings);
    }

    /**
     * For just the current page of guarantor ids, look up the family
     * member names/ids to display. Deliberately NOT done against the full
     * (unpaginated) guarantor set — joining od_patients on Guarantor for
     * every guarantor before pagination is what made this query slow.
     */
    private function attachFamilies(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $ids = $rows->pluck('guarantor_id')->map(fn($id) => (int) $id)->all();

        $families = DB::table('od_patients')
            ->whereIn('Guarantor', $ids)
            ->selectRaw("
                Guarantor as guarantor_id,
                GROUP_CONCAT(DISTINCT CONCAT(LName, ', ', FName) ORDER BY LName SEPARATOR ' | ') as family_names,
                GROUP_CONCAT(DISTINCT PatNum ORDER BY LName SEPARATOR ', ') as family_ids
            ")
            ->groupBy('Guarantor')
            ->get()
            ->keyBy('guarantor_id');

        return $rows->map(function ($row) use ($families) {
            $family = $families->get((int) $row->guarantor_id);
            $row->family_names = $family->family_names ?? null;
            $row->family_ids = $family->family_ids ?? null;
            $row->office = self::OFFICE_NAME;

            return $row;
        });
    }

    private function searchClause(?string $search, string $alias): string
    {
        return $search ? "AND ({$alias}.LName LIKE ? OR {$alias}.FName LIKE ?)" : '';
    }

    private function searchBindings(?string $search): array
    {
        return $search ? ["%{$search}%", "%{$search}%"] : [];
    }

    private function dateBindings(string $asOfDate): array
    {
        // one binding per DATEDIFF(?, ...) usage in baseSql()'s three UNION branches
        return [$asOfDate, $asOfDate, $asOfDate];
    }

    /**
     * Per-guarantor bucketed remaining balance, derived from:
     *  - completed procedures netted against their linked payments/
     *    insurance payments/write-offs/adjustments (aged by ProcDate)
     *  - adjustments not linked to a specific procedure (aged by AdjDate)
     *  - payments not linked to a specific procedure or pay-plan charge,
     *    i.e. unapplied/prepayment credits (aged by DatePay)
     */
    private function baseSql(bool $includeCredits, string $groupBy): string
    {
        $creditsFilter = $includeCredits ? '' : 'AND SUM(items.remaining) > 0';
        $groupExpr = $groupBy === 'patient' ? 'p.PatNum' : 'COALESCE(g.PatNum, p.PatNum)';

        return "
            SELECT
                {$groupExpr} AS row_id,
                MAX(COALESCE(g.PatNum, p.PatNum)) AS guarantor_id,
                SUM(CASE WHEN items.age <= 30 THEN items.remaining ELSE 0 END) AS bal_current,
                SUM(CASE WHEN items.age BETWEEN 31 AND 60 THEN items.remaining ELSE 0 END) AS bal_30,
                SUM(CASE WHEN items.age BETWEEN 61 AND 90 THEN items.remaining ELSE 0 END) AS bal_60,
                SUM(CASE WHEN items.age BETWEEN 91 AND 120 THEN items.remaining ELSE 0 END) AS bal_90,
                SUM(CASE WHEN items.age BETWEEN 121 AND 180 THEN items.remaining ELSE 0 END) AS bal_120,
                SUM(CASE WHEN items.age BETWEEN 181 AND 240 THEN items.remaining ELSE 0 END) AS bal_180,
                SUM(CASE WHEN items.age BETWEEN 241 AND 365 THEN items.remaining ELSE 0 END) AS bal_240,
                SUM(CASE WHEN items.age > 365 THEN items.remaining ELSE 0 END) AS bal_365,
                SUM(items.remaining) AS total
            FROM (
                SELECT pl.PatNum, DATEDIFF(?, pl.ProcDate) AS age,
                    CAST(pl.ProcFee AS DECIMAL(12,2))
                    - COALESCE(ps.paid, 0)
                    - COALESCE(cp.ins_paid, 0)
                    - COALESCE(cp.write_off, 0)
                    - COALESCE(adj.amt, 0) AS remaining
                FROM od_procedure_logs pl
                LEFT JOIN (
                    SELECT ProcNum, SUM(CAST(SplitAmt AS DECIMAL(12,2))) paid
                    FROM od_pay_splits WHERE ProcNum <> 0 GROUP BY ProcNum
                ) ps ON ps.ProcNum = pl.ProcNum
                LEFT JOIN (
                    SELECT ProcNum,
                        SUM(CAST(InsPayAmt AS DECIMAL(12,2))) ins_paid,
                        SUM(CAST(WriteOff AS DECIMAL(12,2))) write_off
                    FROM od_claim_procs GROUP BY ProcNum
                ) cp ON cp.ProcNum = pl.ProcNum
                LEFT JOIN (
                    SELECT ProcNum, SUM(CAST(AdjAmt AS DECIMAL(12,2))) amt
                    FROM od_adjustments WHERE ProcNum <> 0 GROUP BY ProcNum
                ) adj ON adj.ProcNum = pl.ProcNum
                WHERE pl.ProcStatus = 'C'

                UNION ALL

                SELECT a.PatNum, DATEDIFF(?, a.AdjDate) AS age, CAST(a.AdjAmt AS DECIMAL(12,2)) AS remaining
                FROM od_adjustments a
                WHERE a.ProcNum = 0 OR a.ProcNum IS NULL

                UNION ALL

                SELECT s.PatNum, DATEDIFF(?, s.DatePay) AS age, -CAST(s.SplitAmt AS DECIMAL(12,2)) AS remaining
                FROM od_pay_splits s
                WHERE (s.ProcNum = 0 OR s.ProcNum IS NULL)
                  AND (s.PayPlanChargeNum = 0 OR s.PayPlanChargeNum IS NULL)
            ) items
            JOIN od_patients p ON p.PatNum = items.PatNum
            LEFT JOIN od_patients g ON g.PatNum = p.Guarantor
            GROUP BY {$groupExpr}
            HAVING SUM(items.remaining) != 0 {$creditsFilter}
        ";
    }

    /**
     * Outstanding payment-plan ("Contract") principal per guarantor: each
     * charge's Principal minus payments applied against that specific
     * PayPlanChargeNum. Not aged/bucketed and not affected by asOfDate —
     * it reflects the current outstanding contract balance, a distinct
     * obligation from the AR ledger above (mirrors how OpenDental tracks
     * payment plans separately from account aging).
     */
    private function contractSql(): string
    {
        return "
            SELECT
                ppc.Guarantor AS guarantor_id,
                SUM(CAST(ppc.Principal AS DECIMAL(12,2)) - COALESCE(pd.paid, 0)) AS contract
            FROM od_pay_plan_charges ppc
            LEFT JOIN (
                SELECT PayPlanChargeNum, SUM(CAST(SplitAmt AS DECIMAL(12,2))) paid
                FROM od_pay_splits WHERE PayPlanChargeNum <> 0 GROUP BY PayPlanChargeNum
            ) pd ON pd.PayPlanChargeNum = ppc.PayPlanChargeNum
            GROUP BY ppc.Guarantor
        ";
    }
}
