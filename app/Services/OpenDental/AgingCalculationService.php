<?php

namespace App\Services\OpenDental;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\ProcStatus;
use App\Models\Office;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calculates AR aging directly from the OpenDental transactions ledger.
 *
 * OpenDental's per-patient Bal_0_30 / Bal_31_60 / Bal_61_90 / BalOver90
 * fields are empty in this environment, and its 4-bucket model doesn't
 * match the 8-bucket (0-30, 31-60, 61-90, 91-120, 121-180, 181-240,
 * 241-365, >365) resolution required by Jarvis.
 *
 * This service derives aging dynamically by aging each individual
 * completed procedure log, adjustment, and unapplied payment split
 * against the as-of date (DATEDIFF).
 */
class AgingCalculationService
{
    public function __construct(
        private readonly ClinicRegistry $clinics
    ) {}

    /**
     * Map of client-requested DataTables column keys to the SQL expressions
     * or alias names they sort by in the outer query. Keys outside this list
     * fall back to 'total'.
     */
    private const SORTABLE = [
        'guarantor_name' => 'g.LName',
        'patient_name' => 'p.LName',
        'family_names' => 'p.LName',
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
        string $sortDir = 'desc',
        ?int $officeId = null
    ): array {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $totalRecords = $this->countRecords($asOfDate, null, true, 'guarantor', $officeId);
        $filteredRecords = $this->countRecords($asOfDate, $search, $includeCredits, 'guarantor', $officeId);

        $rows = $this->pagedRows($asOfDate, $search, $includeCredits, $start, $length, 'guarantor', $sortKey, $sortDir, $officeId);
        $rows = $this->attachFamilies($rows, $officeId);

        $totals = $this->totals($asOfDate, $search, $includeCredits, 'guarantor', $officeId);

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'data' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Same ledger engine as guarantorAging(), grouped per individual
     * patient instead of rolled up to their guarantor.
     */
    public function patientAging(
        string $asOfDate,
        ?string $search,
        bool $includeCredits,
        int $start,
        int $length,
        ?string $sortKey = null,
        string $sortDir = 'desc',
        ?int $officeId = null
    ): array {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $totalRecords = $this->countRecords($asOfDate, null, true, 'patient', $officeId);
        $filteredRecords = $this->countRecords($asOfDate, $search, $includeCredits, 'patient', $officeId);

        $rows = $this->pagedRows($asOfDate, $search, $includeCredits, $start, $length, 'patient', $sortKey, $sortDir, $officeId);

        $totals = $this->totals($asOfDate, $search, $includeCredits, 'patient', $officeId);

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'data' => $rows,
            'totals' => $totals,
        ];
    }

    private function countRecords(string $asOfDate, ?string $search, bool $includeCredits, string $groupBy, ?int $officeId = null): int
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $joinAlias = $groupBy === 'patient' ? 'p' : 'g';

        $sql = 'SELECT COUNT(*) as cnt FROM ('.$this->baseSql($includeCredits, $groupBy, $officeId).') base
                JOIN od_patients '.$joinAlias.' ON '.$joinAlias.'.PatNum = base.row_id AND '.$joinAlias.'.office_id = ?
                WHERE 1=1 '.$this->searchClause($search, $joinAlias);

        $bindings = array_merge(
            $this->dateBindings($asOfDate),
            [$officeId],
            $this->searchBindings($search)
        );

        return (int) (DB::selectOne($sql, $bindings)->cnt ?? 0);
    }

    private function pagedRows(string $asOfDate, ?string $search, bool $includeCredits, int $start, int $length, string $groupBy, ?string $sortKey = null, string $sortDir = 'desc', ?int $officeId = null): Collection
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
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
                    FROM ('.$this->baseSql($includeCredits, 'patient', $officeId).') base
                    JOIN od_patients p ON p.PatNum = base.row_id AND p.office_id = ?
                    LEFT JOIN od_patients g ON g.PatNum = base.guarantor_id AND g.office_id = ?
                    LEFT JOIN ('.$this->contractSql($officeId).') contract_agg ON contract_agg.guarantor_id = base.guarantor_id
                    WHERE 1=1 '.$this->searchClause($search, 'p')."
                    {$orderBy}
                    LIMIT ? OFFSET ?";

            $bindings = array_merge(
                $this->dateBindings($asOfDate),
                [$officeId, $officeId],
                $this->searchBindings($search),
                [$length, $start]
            );

            return collect(DB::select($sql, $bindings))->map(function ($row) use ($officeId) {
                $row->office = $this->clinics->name(0, $officeId);

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
                FROM ('.$this->baseSql($includeCredits, 'guarantor', $officeId).') base
                JOIN od_patients g ON g.PatNum = base.row_id AND g.office_id = ?
                LEFT JOIN ('.$this->contractSql($officeId).') contract_agg ON contract_agg.guarantor_id = base.row_id
                WHERE 1=1 '.$this->searchClause($search, 'g')."
                {$orderBy}
                LIMIT ? OFFSET ?";

        $bindings = array_merge(
            $this->dateBindings($asOfDate),
            [$officeId],
            $this->searchBindings($search),
            [$length, $start]
        );

        return collect(DB::select($sql, $bindings));
    }

    public function totals(string $asOfDate, ?string $search, bool $includeCredits, string $groupBy, ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        if ($groupBy === 'patient') {
            $innerBase = $this->baseSql($includeCredits, 'patient', $officeId);

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
                                FROM ('.$innerBase.') base2
                                JOIN od_patients p2 ON p2.PatNum = base2.row_id AND p2.office_id = ?
                                WHERE 1=1 '.$this->searchClause($search, 'p2').'
                            ) g_ids
                            LEFT JOIN ('.$this->contractSql($officeId).') contract_agg ON contract_agg.guarantor_id = g_ids.guarantor_id
                        ) AS contract_total,
                        COALESCE(SUM(base.total), 0) AS grand_total
                    FROM ('.$innerBase.') base
                    JOIN od_patients p ON p.PatNum = base.row_id AND p.office_id = ?
                    WHERE 1=1 '.$this->searchClause($search, 'p');

            $bindings = array_merge(
                $this->dateBindings($asOfDate),
                [$officeId],
                $this->searchBindings($search),
                $this->dateBindings($asOfDate),
                [$officeId],
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
                FROM ('.$this->baseSql($includeCredits, 'guarantor', $officeId).') base
                JOIN od_patients g ON g.PatNum = base.row_id AND g.office_id = ?
                LEFT JOIN ('.$this->contractSql($officeId).') contract_agg ON contract_agg.guarantor_id = base.row_id
                WHERE 1=1 '.$this->searchClause($search, 'g');

        $bindings = array_merge($this->dateBindings($asOfDate), [$officeId], $this->searchBindings($search));

        return (array) DB::selectOne($sql, $bindings);
    }

    private function attachFamilies(Collection $rows, ?int $officeId = null): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $officeId = $officeId ?? Office::getActiveOfficeId();
        $ids = $rows->pluck('guarantor_id')->map(fn ($id) => (int) $id)->all();

        $families = DB::table('od_patients')
            ->where('office_id', $officeId)
            ->whereIn('Guarantor', $ids)
            ->selectRaw("
                Guarantor as guarantor_id,
                GROUP_CONCAT(DISTINCT CONCAT(LName, ', ', FName) ORDER BY LName SEPARATOR ' | ') as family_names,
                GROUP_CONCAT(DISTINCT PatNum ORDER BY LName SEPARATOR ', ') as family_ids
            ")
            ->groupBy('Guarantor')
            ->get()
            ->keyBy('guarantor_id');

        return $rows->map(function ($row) use ($families, $officeId) {
            $family = $families->get((int) $row->guarantor_id);
            $row->family_names = $family->family_names ?? null;
            $row->family_ids = $family->family_ids ?? null;
            $row->office = $this->clinics->name(0, $officeId);

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

    private function baseSql(bool $includeCredits, string $groupBy, ?int $officeId = null): string
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $creditsFilter = $includeCredits ? '' : 'AND SUM(items.remaining) > 0';
        $groupExpr = $groupBy === 'patient' ? 'p.PatNum' : 'COALESCE(g.PatNum, p.PatNum)';
        $completed = ProcStatus::inList(ProcStatus::completed());

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
                    FROM od_pay_splits WHERE office_id = {$officeId} AND ProcNum <> 0 GROUP BY ProcNum
                ) ps ON ps.ProcNum = pl.ProcNum
                LEFT JOIN (
                    SELECT ProcNum,
                        SUM(CAST(InsPayAmt AS DECIMAL(12,2))) ins_paid,
                        SUM(CAST(WriteOff AS DECIMAL(12,2))) write_off
                    FROM od_claim_procs WHERE office_id = {$officeId} GROUP BY ProcNum
                ) cp ON cp.ProcNum = pl.ProcNum
                LEFT JOIN (
                    SELECT ProcNum, SUM(CAST(AdjAmt AS DECIMAL(12,2))) amt
                    FROM od_adjustments WHERE office_id = {$officeId} AND ProcNum <> 0 GROUP BY ProcNum
                ) adj ON adj.ProcNum = pl.ProcNum
                WHERE pl.office_id = {$officeId} AND pl.ProcStatus IN ({$completed})

                UNION ALL

                SELECT a.PatNum, DATEDIFF(?, a.AdjDate) AS age, CAST(a.AdjAmt AS DECIMAL(12,2)) AS remaining
                FROM od_adjustments a
                WHERE a.office_id = {$officeId} AND (a.ProcNum = 0 OR a.ProcNum IS NULL)

                UNION ALL

                SELECT s.PatNum, DATEDIFF(?, s.DatePay) AS age, -CAST(s.SplitAmt AS DECIMAL(12,2)) AS remaining
                FROM od_pay_splits s
                WHERE s.office_id = {$officeId}
                  AND (s.ProcNum = 0 OR s.ProcNum IS NULL)
                  AND (s.PayPlanChargeNum = 0 OR s.PayPlanChargeNum IS NULL)
            ) items
            JOIN od_patients p ON p.PatNum = items.PatNum AND p.office_id = {$officeId}
            LEFT JOIN od_patients g ON g.PatNum = p.Guarantor AND g.office_id = {$officeId}
            GROUP BY {$groupExpr}
            HAVING SUM(items.remaining) != 0 {$creditsFilter}
        ";
    }

    private function contractSql(?int $officeId = null): string
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        return "
            SELECT
                ppc.Guarantor AS guarantor_id,
                SUM(CAST(ppc.Principal AS DECIMAL(12,2)) - COALESCE(pd.paid, 0)) AS contract
            FROM od_pay_plan_charges ppc
            LEFT JOIN (
                SELECT PayPlanChargeNum, SUM(CAST(SplitAmt AS DECIMAL(12,2))) paid
                FROM od_pay_splits WHERE office_id = {$officeId} AND PayPlanChargeNum <> 0 GROUP BY PayPlanChargeNum
            ) pd ON pd.PayPlanChargeNum = ppc.PayPlanChargeNum
            WHERE ppc.office_id = {$officeId}
            GROUP BY ppc.Guarantor
        ";
    }
}
