<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds the datasets behind the Operations portal tabs.
 *
 * Each public tab method returns a "spec" the shared table partial can render:
 *   [
 *     'groups'  => [['label' => 'By Office', 'span' => 14], ...],   // grouped header row (optional)
 *     'columns' => [['key' => 'gross', 'label' => 'Gross Prod', 'type' => 'money', 'agg' => 'sum'], ...],
 *     'rows'    => [['location' => '8 Mile', 'gross' => 123.0, ...], ...],
 *     'average' => ['gross' => 123.0, ...],                          // footer "Average:" row
 *     'total'   => ['gross' => 123.0, 'adj_pct' => '--', ...],       // footer "Total:" row
 *   ]
 *
 * Metric definitions follow the conventions already used in DashboardController:
 *   gross      = SUM(ProcFee) where ProcStatus = 'C'      (od_procedure_logs)
 *   adjustment = SUM(AdjAmt)                              (od_adjustments, AdjDate)
 *   writeoff   = SUM(WriteOff)                            (od_claim_procs, ProcDate)
 *   collection = SUM(SplitAmt)                            (od_pay_splits, DatePay)
 *   net        = gross - |adjustment| - |writeoff|
 */
class OperationsAnalyticsService
{
    /** ClinicNum => display name. Single clinic today; extend as locations are added. */
    private array $clinicNames = [
        0 => '8 Mile',
    ];

    /**
     * Offices tab.
     *
     * @param  string  $subtab  default | last-year | diff-last-year | percent-diff-last-year
     * @param  int[]  $clinics  restrict to these ClinicNums (empty = all)
     */
    public function offices(string $start, string $end, string $subtab = 'default', array $clinics = []): array
    {
        $columns = $this->officeColumns();
        $percentDiff = $subtab === 'percent-diff-last-year';

        $calculateAbsoluteTotal = function (array $rows) {
            $totalGross = array_sum(array_column($rows, 'gross'));
            $totalAdjustment = array_sum(array_column($rows, 'adjustment'));
            $totalNet = array_sum(array_column($rows, 'net'));
            $totalCollection = array_sum(array_column($rows, 'collection'));
            $totalPtsVisit = array_sum(array_column($rows, 'pts_visit'));
            $totalUniquePts = array_sum(array_column($rows, 'unique_pts'));
            $totalNptVisit = array_sum(array_column($rows, 'npt_visit'));
            $totalNewPatientDollars = array_sum(array_column($rows, 'new_patient_dollars'));
            $totalWorkingDays = array_sum(array_column($rows, 'working_days'));
            $totalProcedures = array_sum(array_column($rows, 'procedures'));

            $adjPct = $totalGross > 0 ? round($totalAdjustment / $totalGross * 100, 2) : 0;
            $collPct = $totalNet > 0 ? round($totalCollection / $totalNet * 100, 2) : 0;

            $pwdProduction = $totalWorkingDays > 0 ? round($totalNet / $totalWorkingDays, 2) : 0;
            $pwdCollection = $totalWorkingDays > 0 ? round($totalCollection / $totalWorkingDays, 2) : 0;
            $pwdPtsVisit = $totalWorkingDays > 0 ? round($totalPtsVisit / $totalWorkingDays, 2) : 0;
            $pwdNptVisit = $totalWorkingDays > 0 ? round($totalNptVisit / $totalWorkingDays, 2) : 0;

            $ppvProduction = $totalPtsVisit > 0 ? round($totalNet / $totalPtsVisit, 2) : 0;
            $ppvCollection = $totalPtsVisit > 0 ? round($totalCollection / $totalPtsVisit, 2) : 0;
            $ppvProcedures = $totalPtsVisit > 0 ? round($totalProcedures / $totalPtsVisit, 2) : 0;

            $ppProduction = $totalProcedures > 0 ? round($totalNet / $totalProcedures, 2) : 0;
            $ppCollection = $totalProcedures > 0 ? round($totalCollection / $totalProcedures, 2) : 0;

            return [
                'gross' => $totalGross,
                'adjustment' => $totalAdjustment,
                'adj_pct' => $adjPct,
                'net' => $totalNet,
                'collection' => $totalCollection,
                'coll_pct' => $collPct,
                'pts_visit' => $totalPtsVisit,
                'unique_pts' => $totalUniquePts,
                'npt_visit' => $totalNptVisit,
                'new_patient_dollars' => $totalNewPatientDollars,
                'act_pts_reservation' => null,
                'act_pts' => null,
                'retention' => null,
                'working_days' => $totalWorkingDays,
                'pwd_production' => $pwdProduction,
                'pwd_collection' => $pwdCollection,
                'pwd_pts_visit' => $pwdPtsVisit,
                'pwd_npt_visit' => $pwdNptVisit,
                'ppv_production' => $ppvProduction,
                'ppv_collection' => $ppvCollection,
                'ppv_procedures' => $ppvProcedures,
                'pp_production' => $ppProduction,
                'pp_collection' => $ppCollection,
            ];
        };

        if ($subtab === 'last-year') {
            [$start, $end] = $this->shiftYear($start, $end);
            $rows = $this->officeRows($start, $end, $clinics);
            $total = $calculateAbsoluteTotal($rows);
        } elseif ($subtab === 'diff-last-year' || $percentDiff) {
            [$lyStart, $lyEnd] = $this->shiftYear($start, $end);
            $currentRows = $this->officeRows($start, $end, $clinics);
            $lastRows = $this->officeRows($lyStart, $lyEnd, $clinics);

            $current = $this->keyByClinic($currentRows);
            $last = $this->keyByClinic($lastRows);
            $rows = $this->combine($current, $last, $columns, $percentDiff);

            $currentTotal = $calculateAbsoluteTotal($currentRows);
            $lastTotal = $calculateAbsoluteTotal($lastRows);

            $total = [];
            foreach ($columns as $col) {
                $key = $col['key'];
                if (($col['type'] ?? '') === 'text') {
                    continue;
                }
                $a = $currentTotal[$key] ?? null;
                $b = $lastTotal[$key] ?? null;

                if ($a === null || $b === null) {
                    $total[$key] = null;
                } elseif ($percentDiff) {
                    $total[$key] = $b != 0 ? round(($a - $b) / abs($b) * 100, 2) : null;
                } else {
                    $total[$key] = round($a - $b, 2);
                }
            }
        } else {
            $rows = $this->officeRows($start, $end, $clinics);
            $total = $calculateAbsoluteTotal($rows);
        }

        return [
            'groups' => [
                ['label' => 'By Office',         'span' => 14],
                ['label' => 'Per Working Day',   'span' => 4],
                ['label' => 'Per Patient Visit', 'span' => 3],
                ['label' => 'Per Procedure',     'span' => 2],
            ],
            'columns' => $percentDiff ? $this->asPercentColumns($columns) : $columns,
            'rows' => $rows,
            'average' => $this->aggregate($rows, $columns, 'avg'),
            'total' => $total,
        ];
    }

    /**
     * Payors tab — insurance-side breakdown, one row per plan per location.
     *
     * Sourced directly from od_claim_procs (the insurance ledger), because in the
     * current data procedure_logs and claim_procs are synced for disjoint ProcNum
     * ranges and cannot be joined. Payor names require a carrier sync; until then
     * the label is the plan number.
     *
     * @param  int[]  $clinics
     */
    public function payors(string $start, string $end, array $clinics = []): array
    {
        $columns = $this->payorColumns();
        $rows = $this->payorRows($start, $end, $clinics);

        return [
            'groups' => [],
            'columns' => $columns,
            'rows' => $rows,
            'average' => $this->aggregate($rows, $columns, 'avg'),
            'total' => $this->aggregate($rows, $columns, 'total'),
        ];
    }

    private function payorColumns(): array
    {
        return [
            ['key' => 'payor',      'label' => 'Payor',           'type' => 'text',    'sticky' => true],
            ['key' => 'location',   'label' => 'Location',        'type' => 'text'],
            ['key' => 'gross',      'label' => 'Gross Production', 'type' => 'money',   'agg' => 'sum'],
            ['key' => 'net',        'label' => 'Net Production',  'type' => 'money',   'agg' => 'sum'],
            ['key' => 'pct_ttl',    'label' => '% of TTL',        'type' => 'percent', 'heat' => false],
            ['key' => 'adjustment', 'label' => 'Adjustment',      'type' => 'money',   'agg' => 'sum'],
            ['key' => 'collection', 'label' => 'Collection',      'type' => 'money',   'agg' => 'sum'],
            ['key' => 'pts_visits', 'label' => 'Pts Visits',      'type' => 'number',  'agg' => 'sum'],
            ['key' => 'npt_visit',  'label' => 'Npt Visit',       'type' => 'number',  'agg' => 'sum'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function payorRows(string $start, string $end, array $clinics): array
    {
        $concat = $this->concatPatNumProcDate();
        $q = DB::table('od_claim_procs')
            ->selectRaw("PlanNum, ClinicNum,
                SUM(FeeBilled)                                AS gross,
                SUM(WriteOff)                                 AS writeoff,
                SUM(InsPayAmt)                                AS collection,
                COUNT(DISTINCT {$concat}) AS pts_visits")
            ->whereBetween('ProcDate', [$start, $end]);
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }
        $claims = $q->groupBy('PlanNum', 'ClinicNum')->get();

        $npt = $this->newPatientsByPayor($start, $end, $clinics);

        // Total net production drives the "% of TTL" column.
        $totalNet = 0.0;
        $staged = [];
        foreach ($claims as $c) {
            $gross = (float) $c->gross;
            $writeoff = (float) $c->writeoff;
            $net = $gross - abs($writeoff);
            $totalNet += $net;
            $staged[] = [$c, $gross, $writeoff, $net];
        }

        $rows = [];
        foreach ($staged as [$c, $gross, $writeoff, $net]) {
            $key = $c->PlanNum.'|'.$c->ClinicNum;
            $rows[] = [
                'clinic_num' => (int) $c->ClinicNum,
                'payor' => $this->payorLabel($c->PlanNum),
                'location' => $this->clinicNames[(int) $c->ClinicNum] ?? ('Location '.$c->ClinicNum),
                'gross' => round($gross, 2),
                'net' => round($net, 2),
                'pct_ttl' => $totalNet != 0 ? round($net / $totalNet * 100, 2) : 0,
                'adjustment' => round(-abs($writeoff), 2),
                'collection' => round((float) $c->collection, 2),
                'pts_visits' => (int) $c->pts_visits,
                'npt_visit' => (int) ($npt[$key] ?? 0),
            ];
        }

        usort($rows, fn ($a, $b) => $b['net'] <=> $a['net']);

        return $rows;
    }

    /** New patients (first-ever claim in range) grouped by "PlanNum|ClinicNum". */
    private function newPatientsByPayor(string $start, string $end, array $clinics): array
    {
        $firstClaim = DB::table('od_claim_procs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->groupBy('PatNum');

        $q = DB::table('od_claim_procs as cp')
            ->joinSub($firstClaim, 'fc', 'cp.PatNum', '=', 'fc.PatNum')
            ->selectRaw('cp.PlanNum, cp.ClinicNum, COUNT(DISTINCT cp.PatNum) AS npt')
            ->whereBetween('cp.ProcDate', [$start, $end])
            ->whereBetween('fc.first_date', [$start, $end]);
        if ($clinics) {
            $q->whereIn('cp.ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy('cp.PlanNum', 'cp.ClinicNum')->get() as $r) {
            $out[$r->PlanNum.'|'.$r->ClinicNum] = (int) $r->npt;
        }

        return $out;
    }

    /** Human label for a plan. Carrier names require a carrier sync; number is the fallback. */
    private function payorLabel($planNum): string
    {
        return ((int) $planNum) > 0 ? 'Plan '.$planNum : 'No Insurance';
    }

    /**
     * Cancellations tab.
     *
     * @param  string  $subtab  default | diff-last-year | percent-diff-last-year
     * @param  int[]  $clinics
     */
    public function cancellations(string $start, string $end, string $subtab = 'default', array $clinics = []): array
    {
        $columns = $this->cancellationColumns();
        $percentDiff = $subtab === 'percent-diff-last-year';

        if ($subtab === 'diff-last-year' || $percentDiff) {
            [$lyStart, $lyEnd] = $this->shiftYear($start, $end);
            $current = $this->keyByClinic($this->cancellationRows($start, $end, $clinics));
            $last = $this->keyByClinic($this->cancellationRows($lyStart, $lyEnd, $clinics));
            $rows = $this->combine($current, $last, $columns, $percentDiff);
        } else {
            $rows = $this->cancellationRows($start, $end, $clinics);
        }

        return [
            'groups' => [],
            'columns' => $percentDiff ? $this->asPercentColumns($columns) : $columns,
            'rows' => $rows,
            'average' => $this->aggregate($rows, $columns, 'avg'),
            'total' => $this->aggregate($rows, $columns, $percentDiff ? 'avg' : 'total'),
        ];
    }

    /**
     * Production Details tab — production breakdown by location, optionally
     * expanded by provider and/or date via the toggles.
     *
     * @param  string[]  $group  subset of ['provider','date']
     * @param  int[]  $clinics
     */
    public function productionDetails(string $start, string $end, array $group = [], array $clinics = []): array
    {
        $dims = array_values(array_intersect(['provider', 'date'], $group));
        $columns = $this->productionDetailColumns($dims);
        $rows = $this->productionDetailRows($start, $end, $dims, $clinics);

        return [
            'groups' => [
                ['label' => 'By Office',         'span' => 5],
                ['label' => 'Per Working Day',   'span' => 4],
                ['label' => 'Per Patient Visit', 'span' => 3],
                ['label' => 'Per Procedure',     'span' => 2],
            ],
            'columns' => $columns,
            'rows' => $rows,
            'average' => $this->aggregate($rows, $columns, 'avg'),
            'total' => $this->aggregate($rows, $columns, 'total'),
        ];
    }

    private function productionDetailColumns(array $dims): array
    {
        $lead = [['key' => 'location', 'label' => 'Location', 'type' => 'text', 'sticky' => true]];
        if (in_array('provider', $dims, true)) {
            $lead[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
        }
        if (in_array('date', $dims, true)) {
            $lead[] = ['key' => 'date', 'label' => 'Date', 'type' => 'text'];
        }

        return array_merge($lead, [
            // By Office
            ['key' => 'production',     'label' => 'Production',   'type' => 'money',  'agg' => 'sum'],
            ['key' => 'adjustment',     'label' => 'Adjustment',   'type' => 'money',  'agg' => 'sum'],
            ['key' => 'collection',     'label' => 'Collection',   'type' => 'money',  'agg' => 'sum'],
            ['key' => 'pts_visits',     'label' => 'Pts Visits',   'type' => 'number', 'agg' => 'sum'],
            ['key' => 'new_pts_visit',  'label' => 'New Pts Visit', 'type' => 'number', 'agg' => 'sum'],
            // Per Working Day
            ['key' => 'pwd_production', 'label' => 'Production',    'type' => 'money'],
            ['key' => 'pwd_collection', 'label' => 'Collection',   'type' => 'money'],
            ['key' => 'pwd_pts_visit',  'label' => 'Pts Visit',    'type' => 'number'],
            ['key' => 'pwd_npt_visit',  'label' => 'Npt Visit',    'type' => 'number'],
            // Per Patient Visit
            ['key' => 'ppv_production', 'label' => 'Production',    'type' => 'money'],
            ['key' => 'ppv_collection', 'label' => 'Collection',   'type' => 'money'],
            ['key' => 'ppv_procedures', 'label' => 'Procedures',   'type' => 'number'],
            // Per Procedure
            ['key' => 'pp_production',  'label' => 'Production',    'type' => 'money'],
            ['key' => 'pp_collection',  'label' => 'Collection',   'type' => 'money'],
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function productionDetailRows(string $start, string $end, array $dims, array $clinics): array
    {
        $prod = $this->pdGroupedProduction($start, $end, $dims, $clinics);
        $adj = $this->pdGroupedSum('od_adjustments', 'AdjAmt', 'AdjDate', $dims, $start, $end, $clinics);
        $coll = $this->pdGroupedSum('od_pay_splits', 'SplitAmt', 'DatePay', $dims, $start, $end, $clinics);
        $wo = $this->pdGroupedSum('od_claim_procs', 'WriteOff', 'ProcDate', $dims, $start, $end, $clinics);
        $npt = $this->pdGroupedNewPatients($start, $end, $dims, $clinics);

        $keys = array_values(array_unique(array_merge(
            array_keys($prod), array_keys($adj), array_keys($coll)
        )));

        $withProvider = in_array('provider', $dims, true);
        $withDate = in_array('date', $dims, true);
        $providers = $withProvider ? DB::table('od_providers')->get()->keyBy('ProvNum') : collect();

        $rows = [];
        foreach ($keys as $key) {
            $parts = explode('|', $key);
            $i = 0;
            $clinic = $parts[$i++];
            $prov = $withProvider ? ($parts[$i++] ?? null) : null;
            $date = $withDate ? ($parts[$i++] ?? null) : null;

            $p = $prod[$key] ?? null;
            $gross = (float) ($p->gross ?? 0);
            $adjustment = (float) ($adj[$key] ?? 0);
            $writeoff = (float) ($wo[$key] ?? 0);
            $collection = (float) ($coll[$key] ?? 0);
            $net = $gross - abs($adjustment) - abs($writeoff);
            $ptsVisits = (int) ($p->pts_visits ?? 0);
            $procedures = (int) ($p->procedures ?? 0);
            $workingDays = (int) ($p->working_days ?? 0);
            $nptVisits = (int) ($npt[$key] ?? 0);

            $row = [
                'row_key' => $key,
                'clinic_num' => (int) $clinic,
                'location' => $this->clinicNames[(int) $clinic] ?? ('Location '.$clinic),
                'production' => round($net, 2),
                'adjustment' => round($adjustment, 2),
                'collection' => round($collection, 2),
                'pts_visits' => $ptsVisits,
                'new_pts_visit' => $nptVisits,
                'pwd_production' => $workingDays > 0 ? round($net / $workingDays, 2) : 0,
                'pwd_collection' => $workingDays > 0 ? round($collection / $workingDays, 2) : 0,
                'pwd_pts_visit' => $workingDays > 0 ? round($ptsVisits / $workingDays, 2) : 0,
                'pwd_npt_visit' => $workingDays > 0 ? round($nptVisits / $workingDays, 2) : 0,
                'ppv_production' => $ptsVisits > 0 ? round($net / $ptsVisits, 2) : 0,
                'ppv_collection' => $ptsVisits > 0 ? round($collection / $ptsVisits, 2) : 0,
                'ppv_procedures' => $ptsVisits > 0 ? round($procedures / $ptsVisits, 2) : 0,
                'pp_production' => $procedures > 0 ? round($net / $procedures, 2) : 0,
                'pp_collection' => $procedures > 0 ? round($collection / $procedures, 2) : 0,
            ];

            if ($withProvider) {
                $pv = $providers[$prov] ?? null;
                $row['provider'] = $pv
                    ? trim(($pv->LName ?? '').(($pv->LName && $pv->PName) ? ', ' : '').($pv->PName ?? ''))
                    : ('Provider '.$prov);
                if ($row['provider'] === '') {
                    $row['provider'] = 'Provider '.$prov;
                }
            }
            if ($withDate) {
                $row['date'] = $date;
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => ($b['production'] <=> $a['production']));

        return $rows;
    }

    /** Completed-procedure metrics grouped by the active dimensions. Keyed by composite. */
    private function pdGroupedProduction(string $start, string $end, array $dims, array $clinics): array
    {
        $concat = $this->concatPatNumProcDate();
        $q = DB::table('od_procedure_logs')
            ->selectRaw("ClinicNum,
                SUM(ProcFee)                                  AS gross,
                COUNT(*)                                      AS procedures,
                COUNT(DISTINCT {$concat}) AS pts_visits,
                COUNT(DISTINCT ProcDate)                      AS working_days")
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end]);

        $groupCols = ['ClinicNum'];
        if (in_array('provider', $dims, true)) {
            $q->addSelect('ProvNum');
            $groupCols[] = 'ProvNum';
        }
        if (in_array('date', $dims, true)) {
            $q->selectRaw('ProcDate AS grp_date');
            $groupCols[] = 'ProcDate';
        }
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy($groupCols)->get() as $r) {
            $out[$this->pdKey($r, $dims)] = $r;
        }

        return $out;
    }

    /** SUM(amount) grouped by the active dimensions. Keyed by composite. */
    private function pdGroupedSum(string $table, string $amountCol, string $dateCol, array $dims, string $start, string $end, array $clinics): array
    {
        $q = DB::table($table)
            ->selectRaw("ClinicNum, SUM($amountCol) AS total")
            ->whereBetween($dateCol, [$start, $end]);

        $groupCols = ['ClinicNum'];
        if (in_array('provider', $dims, true)) {
            $q->addSelect('ProvNum');
            $groupCols[] = 'ProvNum';
        }
        if (in_array('date', $dims, true)) {
            $q->selectRaw("$dateCol AS grp_date");
            $groupCols[] = $dateCol;
        }
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy($groupCols)->get() as $r) {
            $out[$this->pdKey($r, $dims)] = (float) $r->total;
        }

        return $out;
    }

    /** New-patient visits grouped by the active dimensions. Keyed by composite. */
    private function pdGroupedNewPatients(string $start, string $end, array $dims, array $clinics): array
    {
        $firstVisit = DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->where('ProcStatus', 'C')
            ->groupBy('PatNum');

        $q = DB::table('od_procedure_logs as pl')
            ->joinSub($firstVisit, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
            ->selectRaw('pl.ClinicNum, COUNT(DISTINCT pl.PatNum) AS npt')
            ->where('pl.ProcStatus', 'C')
            ->whereBetween('pl.ProcDate', [$start, $end])
            ->whereBetween('fv.first_date', [$start, $end]);

        $groupCols = ['pl.ClinicNum'];
        if (in_array('provider', $dims, true)) {
            $q->addSelect('pl.ProvNum');
            $groupCols[] = 'pl.ProvNum';
        }
        if (in_array('date', $dims, true)) {
            // Count a new patient on their first visit date only.
            $q->whereColumn('pl.ProcDate', 'fv.first_date')->selectRaw('pl.ProcDate AS grp_date');
            $groupCols[] = 'pl.ProcDate';
        }
        if ($clinics) {
            $q->whereIn('pl.ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy($groupCols)->get() as $r) {
            $out[$this->pdKey($r, $dims)] = (int) $r->npt;
        }

        return $out;
    }

    /** Build the composite key (clinic[|prov][|date]) from a grouped result row. */
    private function pdKey(object $r, array $dims): string
    {
        $parts = [$r->ClinicNum];
        if (in_array('provider', $dims, true)) {
            $parts[] = $r->ProvNum;
        }
        if (in_array('date', $dims, true)) {
            $parts[] = substr((string) $r->grp_date, 0, 10);
        }

        return implode('|', $parts);
    }

    /**
     * Providers tab — one row per provider per location.
     *
     * @param  string  $subtab  default | diff-last-year | percent-diff-last-year
     * @param  int[]  $clinics
     */
    public function providers(string $start, string $end, string $subtab = 'default', array $clinics = []): array
    {
        $columns = $this->providerColumns();
        $percentDiff = $subtab === 'percent-diff-last-year';

        if ($subtab === 'diff-last-year' || $percentDiff) {
            [$lyStart, $lyEnd] = $this->shiftYear($start, $end);
            $current = $this->keyByField($this->providerRows($start, $end, $clinics), 'row_key');
            $last = $this->keyByField($this->providerRows($lyStart, $lyEnd, $clinics), 'row_key');
            $rows = $this->combine($current, $last, $columns, $percentDiff);
        } else {
            $rows = $this->providerRows($start, $end, $clinics);
        }

        return [
            'groups' => [
                ['label' => 'By Provider',       'span' => 9],
                ['label' => 'Per Working Day',   'span' => 4],
                ['label' => 'Per Patient Visit', 'span' => 3],
                ['label' => 'Per Procedure',     'span' => 2],
                ['label' => 'Provider Goals',    'span' => 3],
            ],
            'columns' => $percentDiff ? $this->asPercentColumns($columns) : $columns,
            'rows' => $rows,
            'average' => $this->aggregate($rows, $columns, 'avg'),
            'total' => $this->aggregate($rows, $columns, $percentDiff ? 'avg' : 'total'),
        ];
    }

    private function providerColumns(): array
    {
        return [
            ['key' => 'location',          'label' => 'Location',          'type' => 'text', 'sticky' => true],
            ['key' => 'provider',          'label' => 'Provider',          'type' => 'text'],
            ['key' => 'provider_id',       'label' => 'Provider ID',       'type' => 'text'],
            // By Provider
            ['key' => 'gross',             'label' => 'Gross Production',   'type' => 'money',  'agg' => 'sum'],
            ['key' => 'net',               'label' => 'Net Production',     'type' => 'money',  'agg' => 'sum'],
            ['key' => 'adjustment',        'label' => 'Adjustment',        'type' => 'money',  'agg' => 'sum'],
            ['key' => 'collection',        'label' => 'Collection',        'type' => 'money',  'agg' => 'sum'],
            ['key' => 'pts_visits',        'label' => 'Pts Visits',        'type' => 'number', 'agg' => 'sum'],
            ['key' => 'npt_visits',        'label' => 'Npt Visits',        'type' => 'number', 'agg' => 'sum'],
            ['key' => 'working_days',      'label' => 'Working Days',      'type' => 'number'],
            ['key' => 'procedures',        'label' => 'Procedures',        'type' => 'number', 'agg' => 'sum'],
            ['key' => 'retention',         'label' => 'Retention',         'type' => 'percent'],
            // Per Working Day
            ['key' => 'pwd_production',    'label' => 'Production',         'type' => 'money'],
            ['key' => 'pwd_collection',    'label' => 'Collection',        'type' => 'money'],
            ['key' => 'pwd_pts_visits',    'label' => 'Pts Visits',        'type' => 'number'],
            ['key' => 'pwd_npt_visits',    'label' => 'Npt Visits',        'type' => 'number'],
            // Per Patient Visit
            ['key' => 'ppv_production',    'label' => 'Production',         'type' => 'money'],
            ['key' => 'ppv_collection',    'label' => 'Collection',        'type' => 'money'],
            ['key' => 'ppv_procedures',    'label' => 'Procedures',        'type' => 'number'],
            // Per Procedure
            ['key' => 'pp_production',     'label' => 'Production',         'type' => 'money'],
            ['key' => 'pp_collection',     'label' => 'Collection',        'type' => 'money'],
            // Provider Goals
            ['key' => 'production_goal',   'label' => 'Production Goal',    'type' => 'money'],
            ['key' => 'actual_production', 'label' => 'Actual Production',  'type' => 'money',  'agg' => 'sum'],
            ['key' => 'variance',          'label' => 'Variance',          'type' => 'money'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function providerRows(string $start, string $end, array $clinics): array
    {
        // Production-side metrics grouped by clinic + provider.
        $concat = $this->concatPatNumProcDate();
        $prodQ = DB::table('od_procedure_logs')
            ->selectRaw("ClinicNum, ProvNum,
                SUM(ProcFee)                                  AS gross,
                COUNT(*)                                      AS procedures,
                COUNT(DISTINCT {$concat}) AS pts_visits,
                COUNT(DISTINCT ProcDate)                      AS working_days")
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end]);
        if ($clinics) {
            $prodQ->whereIn('ClinicNum', $clinics);
        }
        $prod = $prodQ->groupBy('ClinicNum', 'ProvNum')->get();

        $adj = $this->sumByClinicProvider('od_adjustments', 'AdjAmt', 'AdjDate', $start, $end, $clinics);
        $wo = $this->sumByClinicProvider('od_claim_procs', 'WriteOff', 'ProcDate', $start, $end, $clinics);
        $col = $this->sumByClinicProvider('od_pay_splits', 'SplitAmt', 'DatePay', $start, $end, $clinics);
        $npt = $this->newPatientsByClinicProvider($start, $end, $clinics);
        $hours = $this->scheduledHoursByClinicProvider($start, $end, $clinics);

        $providers = DB::table('od_providers')->get()->keyBy('ProvNum');

        $rows = [];
        foreach ($prod as $p) {
            $key = $p->ClinicNum.'|'.$p->ProvNum;
            $gross = (float) $p->gross;
            $adjustment = (float) ($adj[$key] ?? 0);
            $writeoff = (float) ($wo[$key] ?? 0);
            $collection = (float) ($col[$key] ?? 0);
            $net = $gross - abs($adjustment) - abs($writeoff);
            $ptsVisits = (int) $p->pts_visits;
            $procedures = (int) $p->procedures;
            $workingDays = (int) $p->working_days;
            $nptVisits = (int) ($npt[$key] ?? 0);

            $prov = $providers[$p->ProvNum] ?? null;
            $name = $prov
                ? trim(($prov->LName ?? '').(($prov->LName && $prov->PName) ? ', ' : '').($prov->PName ?? ''))
                : ('Provider '.$p->ProvNum);

            // Production Goal = Hourly Goal (OpenDental) × scheduled hours in range.
            // Null when either input is missing (matches Jarvis "goal can't calculate").
            $hourlyGoal = (float) ($prov->HourlyProdGoalAmt ?? 0);
            $schedHours = (float) ($hours[$key] ?? 0);
            $goal = ($hourlyGoal > 0 && $schedHours > 0) ? round($hourlyGoal * $schedHours, 2) : null;

            $rows[] = [
                'row_key' => $key,
                'clinic_num' => (int) $p->ClinicNum,
                'location' => $this->clinicNames[(int) $p->ClinicNum] ?? ('Location '.$p->ClinicNum),
                'provider' => $name !== '' ? $name : ('Provider '.$p->ProvNum),
                'provider_id' => $p->ProvNum.($prov && $prov->Abbr ? ' - '.$prov->Abbr : ''),
                'gross' => round($gross, 2),
                'net' => round($net, 2),
                'adjustment' => round($adjustment, 2),
                'collection' => round($collection, 2),
                'pts_visits' => $ptsVisits,
                'npt_visits' => $nptVisits,
                'working_days' => $workingDays,
                'procedures' => $procedures,
                'retention' => null, // business rule pending
                'pwd_production' => $workingDays > 0 ? round($net / $workingDays, 2) : 0,
                'pwd_collection' => $workingDays > 0 ? round($collection / $workingDays, 2) : 0,
                'pwd_pts_visits' => $workingDays > 0 ? round($ptsVisits / $workingDays, 2) : 0,
                'pwd_npt_visits' => $workingDays > 0 ? round($nptVisits / $workingDays, 2) : 0,
                'ppv_production' => $ptsVisits > 0 ? round($net / $ptsVisits, 2) : 0,
                'ppv_collection' => $ptsVisits > 0 ? round($collection / $ptsVisits, 2) : 0,
                'ppv_procedures' => $ptsVisits > 0 ? round($procedures / $ptsVisits, 2) : 0,
                'pp_production' => $procedures > 0 ? round($net / $procedures, 2) : 0,
                'pp_collection' => $procedures > 0 ? round($collection / $procedures, 2) : 0,
                'production_goal' => $goal,
                'actual_production' => round($net, 2),
                'variance' => $goal !== null ? round($net - $goal, 2) : null,
            ];
        }

        // Highest producers first, matching Jarvis default ordering.
        usort($rows, fn ($a, $b) => $b['gross'] <=> $a['gross']);

        return $rows;
    }

    /** SUM(amount) grouped by "ClinicNum|ProvNum". */
    private function sumByClinicProvider(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics): array
    {
        $q = DB::table($table)
            ->selectRaw("ClinicNum, ProvNum, SUM($amountCol) AS total")
            ->whereBetween($dateCol, [$start, $end]);
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy('ClinicNum', 'ProvNum')->get() as $r) {
            $out[$r->ClinicNum.'|'.$r->ProvNum] = (float) $r->total;
        }

        return $out;
    }

    /**
     * Provider scheduled hours in range, grouped by "ClinicNum|ProvNum".
     * Source: OpenDental Schedules (SchedType 0 = Provider). Feeds provider goal calc.
     */
    private function scheduledHoursByClinicProvider(string $start, string $end, array $clinics): array
    {
        $q = DB::table('od_schedules')
            ->selectRaw('ClinicNum, ProvNum, SUM(TIME_TO_SEC(StopTime) - TIME_TO_SEC(StartTime)) / 3600 AS hours')
            ->where('SchedType', 0)
            ->where('ProvNum', '>', 0)
            ->whereBetween('SchedDate', [$start, $end]);
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy('ClinicNum', 'ProvNum')->get() as $r) {
            $out[$r->ClinicNum.'|'.$r->ProvNum] = (float) $r->hours;
        }

        return $out;
    }

    /** New-patient visit counts grouped by "ClinicNum|ProvNum". */
    private function newPatientsByClinicProvider(string $start, string $end, array $clinics): array
    {
        $firstVisit = DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->where('ProcStatus', 'C')
            ->groupBy('PatNum');

        $q = DB::table('od_procedure_logs as pl')
            ->joinSub($firstVisit, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
            ->selectRaw('pl.ClinicNum, pl.ProvNum, COUNT(DISTINCT pl.PatNum) AS npt')
            ->where('pl.ProcStatus', 'C')
            ->whereBetween('pl.ProcDate', [$start, $end])
            ->whereBetween('fv.first_date', [$start, $end]);
        if ($clinics) {
            $q->whereIn('pl.ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy('pl.ClinicNum', 'pl.ProvNum')->get() as $r) {
            $out[$r->ClinicNum.'|'.$r->ProvNum] = (int) $r->npt;
        }

        return $out;
    }

    private function cancellationColumns(): array
    {
        return [
            ['key' => 'location',                         'label' => 'Location',                  'type' => 'text',    'sticky' => true],
            ['key' => 'cancellation',                     'label' => 'Cancellation',              'type' => 'number',  'agg' => 'sum', 'heat' => 'invert'],
            ['key' => 'cancellation_dollars',             'label' => 'Cancellation $',            'type' => 'money',   'agg' => 'sum', 'heat' => 'invert'],
            ['key' => 'cancellation_rescheduled',         'label' => 'Cancellation Rescheduled',  'type' => 'number',  'agg' => 'sum'],
            ['key' => 'cancellation_rescheduled_dollars', 'label' => 'Cancellation Rescheduled $', 'type' => 'money',   'agg' => 'sum'],
            ['key' => 'cancellation_pct',                 'label' => '% Cancellation',            'type' => 'percent', 'heat' => 'invert'],
            ['key' => 'rescheduled_pct',                  'label' => '% Rescheduled',             'type' => 'percent'],
            ['key' => 'total_appointments',               'label' => 'Total Appointments Count',  'type' => 'number',  'agg' => 'sum'],
        ];
    }

    /**
     * Cancellations are broken appointments (AptStatus = 5). Date is the ISO string
     * AptDateTime, so we compare on its leading YYYY-MM-DD. Rescheduling is not
     * derivable from the current dataset (NextAptNum is empty) → left null.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cancellationRows(string $start, string $end, array $clinics): array
    {
        $totals = $this->countAppointments($start, $end, $clinics, null);
        $broken = $this->countAppointments($start, $end, $clinics, '5');

        // Cancellation $ = production tied to cancelled/no-show (Broken) appointments.
        // Procedures link to an appointment via AptNum (scheduled) or PlannedAptNum (planned).
        $dollarsQ = DB::table('od_appointments as a')
            ->join('od_procedure_logs as pl', function ($join) {
                $join->on('pl.AptNum', '=', 'a.AptNum')
                    ->orOn('pl.PlannedAptNum', '=', 'a.AptNum');
            })
            ->where('a.AptStatus', '5')
            ->whereRaw('LEFT(a.AptDateTime, 10) BETWEEN ? AND ?', [$start, $end])
            ->selectRaw('a.ClinicNum, SUM(pl.ProcFee) AS dollars');
        if ($clinics) {
            $dollarsQ->whereIn('a.ClinicNum', $clinics);
        }
        $dollars = $dollarsQ->groupBy('a.ClinicNum')->pluck('dollars', 'ClinicNum')->all();

        $clinicNums = array_values(array_unique(array_keys($totals)));
        sort($clinicNums);

        $rows = [];
        foreach ($clinicNums as $c) {
            $cancellation = (int) ($broken[$c] ?? 0);
            $total = (int) ($totals[$c] ?? 0);

            $rows[] = [
                'clinic_num' => (int) $c,
                'location' => $this->clinicNames[(int) $c] ?? ('Location '.$c),
                'cancellation' => $cancellation,
                'cancellation_dollars' => round((float) ($dollars[$c] ?? 0), 2),
                'cancellation_rescheduled' => null, // rescheduling rule pending
                'cancellation_rescheduled_dollars' => null,
                'cancellation_pct' => $total > 0 ? round($cancellation / $total * 100, 2) : 0,
                'rescheduled_pct' => null,
                'total_appointments' => $total,
            ];
        }

        return $rows;
    }

    /** Count appointments in range grouped by ClinicNum, optionally for one AptStatus. */
    private function countAppointments(string $start, string $end, array $clinics, ?string $status): array
    {
        $q = DB::table('od_appointments')
            ->selectRaw('ClinicNum, COUNT(*) AS total')
            ->whereRaw('LEFT(AptDateTime, 10) BETWEEN ? AND ?', [$start, $end]);

        if ($status !== null) {
            $q->where('AptStatus', $status);
        }
        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        return $q->groupBy('ClinicNum')->pluck('total', 'ClinicNum')->all();
    }

    /* ─────────────────────────────────────────────────────────────
     |  Offices — column definitions
     ────────────────────────────────────────────────────────────── */

    private function officeColumns(): array
    {
        return [
            ['key' => 'location',            'label' => 'Location',              'type' => 'text',    'sticky' => true],
            // By Office
            ['key' => 'gross',               'label' => 'Gross Prod',            'type' => 'money',   'agg' => 'sum'],
            ['key' => 'adjustment',          'label' => 'Adjustment',            'type' => 'money',   'agg' => 'sum'],
            ['key' => 'adj_pct',             'label' => 'Adjustment % of Prod',  'type' => 'percent'],
            ['key' => 'net',                 'label' => 'Net Prod',              'type' => 'money',   'agg' => 'sum'],
            ['key' => 'collection',          'label' => 'Collection',            'type' => 'money',   'agg' => 'sum'],
            ['key' => 'coll_pct',            'label' => 'Collection %',          'type' => 'percent'],
            ['key' => 'pts_visit',           'label' => 'Pts Visit',             'type' => 'number',  'agg' => 'sum'],
            ['key' => 'unique_pts',          'label' => '# of Unique Pts',       'type' => 'number',  'agg' => 'sum'],
            ['key' => 'npt_visit',           'label' => 'Npt Visit',             'type' => 'number',  'agg' => 'sum'],
            ['key' => 'new_patient_dollars', 'label' => 'New Patient $',         'type' => 'money',   'agg' => 'sum'],
            ['key' => 'act_pts_reservation', 'label' => 'Act Pts w/ Reservation', 'type' => 'number',  'agg' => 'sum'],
            ['key' => 'act_pts',             'label' => 'Act Pts',               'type' => 'number',  'agg' => 'sum'],
            ['key' => 'retention',           'label' => 'Retention',             'type' => 'percent'],
            ['key' => 'working_days',        'label' => 'Working Days',          'type' => 'number'],
            // Per Working Day
            ['key' => 'pwd_production',       'label' => 'Production',            'type' => 'money'],
            ['key' => 'pwd_collection',       'label' => 'Collection',           'type' => 'money'],
            ['key' => 'pwd_pts_visit',        'label' => 'Pts Visit',            'type' => 'number'],
            ['key' => 'pwd_npt_visit',        'label' => 'Npt Visit',            'type' => 'number'],
            // Per Patient Visit
            ['key' => 'ppv_production',        'label' => 'Production',           'type' => 'money'],
            ['key' => 'ppv_collection',        'label' => 'Collection',          'type' => 'money'],
            ['key' => 'ppv_procedures',        'label' => 'Procedures',          'type' => 'number'],
            // Per Procedure
            ['key' => 'pp_production',         'label' => 'Production',           'type' => 'money'],
            ['key' => 'pp_collection',         'label' => 'Collection',          'type' => 'money'],
        ];
    }

    /* ─────────────────────────────────────────────────────────────
     |  Offices — row builder
     ────────────────────────────────────────────────────────────── */

    /** @return array<int, array<string, mixed>> */
    private function officeRows(string $start, string $end, array $clinics): array
    {
        $prod = $this->productionMetrics($start, $end, $clinics);
        $adj = $this->sumByClinic('od_adjustments', 'AdjAmt', 'AdjDate', $start, $end, $clinics);
        $coll = $this->sumByClinic('od_pay_splits', 'SplitAmt', 'DatePay', $start, $end, $clinics);
        $wo = $this->sumByClinic('od_claim_procs', 'WriteOff', 'ProcDate', $start, $end, $clinics);
        $newp = $this->newPatientMetrics($start, $end, $clinics);

        $clinicNums = array_values(array_unique(array_merge(
            array_keys($prod),
            array_keys($adj),
            array_keys($coll)
        )));
        sort($clinicNums);

        $rows = [];
        foreach ($clinicNums as $c) {
            $p = $prod[$c] ?? null;
            $gross = (float) ($p->gross ?? 0);
            $adjustment = (float) ($adj[$c] ?? 0);
            $writeoff = (float) ($wo[$c] ?? 0);
            $collection = (float) ($coll[$c] ?? 0);
            $net = $gross - abs($adjustment) - abs($writeoff);
            $ptsVisit = (int) ($p->pts_visit ?? 0);
            $procedures = (int) ($p->procedures ?? 0);
            $workingDays = (int) ($p->working_days ?? 0);
            $npt = (int) ($newp[$c]['npt_visit'] ?? 0);
            $nptDollars = (float) ($newp[$c]['new_patient_dollars'] ?? 0);

            $rows[] = [
                'clinic_num' => (int) $c,
                'location' => $this->clinicNames[(int) $c] ?? ('Location '.$c),
                'gross' => round($gross, 2),
                'adjustment' => round($adjustment, 2),
                'adj_pct' => $gross > 0 ? round($adjustment / $gross * 100, 2) : 0,
                'net' => round($net, 2),
                'collection' => round($collection, 2),
                'coll_pct' => $net > 0 ? round($collection / $net * 100, 2) : 0,
                'pts_visit' => $ptsVisit,
                'unique_pts' => (int) ($p->unique_pts ?? 0),
                'npt_visit' => $npt,
                'new_patient_dollars' => round($nptDollars, 2),
                'act_pts_reservation' => null, // business rule pending — see controller notes
                'act_pts' => null,
                'retention' => null,
                'procedures' => $procedures,
                'working_days' => $workingDays,
                'pwd_production' => $workingDays > 0 ? round($net / $workingDays, 2) : 0,
                'pwd_collection' => $workingDays > 0 ? round($collection / $workingDays, 2) : 0,
                'pwd_pts_visit' => $workingDays > 0 ? round($ptsVisit / $workingDays, 2) : 0,
                'pwd_npt_visit' => $workingDays > 0 ? round($npt / $workingDays, 2) : 0,
                'ppv_production' => $ptsVisit > 0 ? round($net / $ptsVisit, 2) : 0,
                'ppv_collection' => $ptsVisit > 0 ? round($collection / $ptsVisit, 2) : 0,
                'ppv_procedures' => $ptsVisit > 0 ? round($procedures / $ptsVisit, 2) : 0,
                'pp_production' => $procedures > 0 ? round($net / $procedures, 2) : 0,
                'pp_collection' => $procedures > 0 ? round($collection / $procedures, 2) : 0,
            ];
        }

        return $rows;
    }

    /** Production-side metrics from completed procedures, keyed by ClinicNum. */
    private function productionMetrics(string $start, string $end, array $clinics): array
    {
        $concat = $this->concatPatNumProcDate();
        $q = DB::table('od_procedure_logs')
            ->selectRaw("ClinicNum,
                SUM(ProcFee)                                  AS gross,
                COUNT(*)                                      AS procedures,
                COUNT(DISTINCT PatNum)                        AS unique_pts,
                COUNT(DISTINCT {$concat}) AS pts_visit,
                COUNT(DISTINCT ProcDate)                      AS working_days")
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end]);

        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        return $q->groupBy('ClinicNum')->get()->keyBy('ClinicNum')->all();
    }

    /** New patients = first-ever completed procedure falls in range; dollars = their production in range. */
    private function newPatientMetrics(string $start, string $end, array $clinics): array
    {
        $firstVisit = DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->where('ProcStatus', 'C')
            ->groupBy('PatNum');

        $q = DB::table('od_procedure_logs as pl')
            ->joinSub($firstVisit, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
            ->selectRaw('pl.ClinicNum,
                COUNT(DISTINCT pl.PatNum) AS npt_visit,
                SUM(pl.ProcFee)           AS new_patient_dollars')
            ->where('pl.ProcStatus', 'C')
            ->whereBetween('pl.ProcDate', [$start, $end])
            ->whereBetween('fv.first_date', [$start, $end]);

        if ($clinics) {
            $q->whereIn('pl.ClinicNum', $clinics);
        }

        $out = [];
        foreach ($q->groupBy('pl.ClinicNum')->get() as $r) {
            $out[$r->ClinicNum] = [
                'npt_visit' => (int) $r->npt_visit,
                'new_patient_dollars' => (float) $r->new_patient_dollars,
            ];
        }

        return $out;
    }

    /** SUM(amount) grouped by ClinicNum for a table/date column. Returns [ClinicNum => total]. */
    private function sumByClinic(string $table, string $amountCol, string $dateCol, string $start, string $end, array $clinics): array
    {
        $q = DB::table($table)
            ->selectRaw("ClinicNum, SUM($amountCol) AS total")
            ->whereBetween($dateCol, [$start, $end]);

        if ($clinics) {
            $q->whereIn('ClinicNum', $clinics);
        }

        return $q->groupBy('ClinicNum')->pluck('total', 'ClinicNum')->all();
    }

    /* ─────────────────────────────────────────────────────────────
     |  Subtab helpers (Last Year / Diff / Percent Diff)
     ────────────────────────────────────────────────────────────── */

    /** Shift a [start,end] range back exactly one year. */
    private function shiftYear(string $start, string $end): array
    {
        return [
            Carbon::parse($start)->subYear()->toDateString(),
            Carbon::parse($end)->subYear()->toDateString(),
        ];
    }

    /** @return array<int|string, array<string,mixed>> keyed by clinic_num */
    private function keyByClinic(array $rows): array
    {
        return $this->keyByField($rows, 'clinic_num');
    }

    /** @return array<int|string, array<string,mixed>> keyed by the given row field */
    private function keyByField(array $rows, string $field): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$row[$field]] = $row;
        }

        return $out;
    }

    /**
     * Build diff / percent-diff rows from current vs last-year row maps.
     * Text columns pass through from current (falling back to last year).
     */
    private function combine(array $current, array $last, array $columns, bool $percent): array
    {
        $clinicNums = array_values(array_unique(array_merge(array_keys($current), array_keys($last))));
        sort($clinicNums);

        $rows = [];
        foreach ($clinicNums as $c) {
            $cur = $current[$c] ?? [];
            $ly = $last[$c] ?? [];
            $row = ['clinic_num' => (int) $c];

            foreach ($columns as $col) {
                $key = $col['key'];

                if (($col['type'] ?? '') === 'text') {
                    $row[$key] = $cur[$key] ?? $ly[$key] ?? null;

                    continue;
                }

                $a = $cur[$key] ?? null;
                $b = $ly[$key] ?? null;

                if ($a === null || $b === null) {
                    $row[$key] = null;
                } elseif ($percent) {
                    $row[$key] = $b != 0 ? round(($a - $b) / abs($b) * 100, 2) : null;
                } else {
                    $row[$key] = round($a - $b, 2);
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** Clone columns but render every numeric column as a percent (used by percent-diff subtab). */
    private function asPercentColumns(array $columns): array
    {
        return array_map(function ($col) {
            if (($col['type'] ?? '') !== 'text') {
                $col['type'] = 'percent';
            }

            return $col;
        }, $columns);
    }

    /* ─────────────────────────────────────────────────────────────
     |  Footer aggregates
     ────────────────────────────────────────────────────────────── */

    /**
     * @param  string  $mode  avg = mean of non-null values; total = sum where agg=sum else '--'
     */
    private function aggregate(array $rows, array $columns, string $mode): array
    {
        $out = [];

        foreach ($columns as $col) {
            $key = $col['key'];

            if (($col['type'] ?? '') === 'text') {
                continue; // label cell is supplied by the partial
            }

            $values = [];
            foreach ($rows as $row) {
                if (isset($row[$key]) && $row[$key] !== null) {
                    $values[] = (float) $row[$key];
                }
            }

            if ($mode === 'total') {
                $out[$key] = ($col['agg'] ?? null) === 'sum' && $values
                    ? round(array_sum($values), 2)
                    : '--';
            } else { // avg
                $out[$key] = $values ? round(array_sum($values) / count($values), 2) : null;
            }
        }

        return $out;
    }

    private function concatPatNumProcDate(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "PatNum || '|' || ProcDate"
            : "CONCAT(PatNum, '|', ProcDate)";
    }
}
