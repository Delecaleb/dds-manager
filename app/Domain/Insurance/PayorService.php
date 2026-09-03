<?php

namespace App\Domain\Insurance;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use App\Models\Office;
use App\Services\OpenDental\OpenDentalClient;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for payor (insurance plan) identity and payor-sliced metrics.
 *
 * Owns the two primitives that were duplicated across the Operations analytics:
 *   - planForPatientSubquery(): the patient -> plan mapping (D10)
 *   - payorLabel(): the plan -> display name
 * so every payor-sliced number resolves plans the same way.
 */
class PayorService
{
    public function __construct(
        private readonly ProductionService $production,
    ) {}

    /**
     * The patient -> plan map: each patient's latest PlanNum from claim procs.
     * Join against this (leftJoinSub) to attribute a patient's activity to a payor.
     */
    public function planForPatientSubquery(?int $officeId = null): Builder
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return DB::table('od_claim_procs as cp')
                ->where('cp.office_id', $officeId)
                ->where('cp.PlanNum', '>', 0)
                ->whereRaw("cp.ClaimProcNum = (SELECT MAX(cp2.ClaimProcNum) FROM od_claim_procs cp2 WHERE cp2.office_id = {$officeId} AND cp2.PatNum = cp.PatNum AND cp2.PlanNum > 0)")
                ->select('cp.PatNum', 'cp.PlanNum');
        }

        return DB::table('od_claim_procs')
            ->where('office_id', $officeId)
            ->selectRaw('PatNum, CAST(SUBSTRING_INDEX(GROUP_CONCAT(PlanNum ORDER BY ClaimProcNum ASC), ",", -1) AS UNSIGNED) AS PlanNum')
            ->where('PlanNum', '>', 0)
            ->groupBy('PatNum');
    }

    /** Display label for a plan (carrier name - carrier num), resolved via the database or OpenDental API. */
    public function payorLabel(int|string $planNum, ?int $officeId = null): string
    {
        $planNum = (int) $planNum;
        if ($planNum === 0) {
            return 'No Insurance - 999999';
        }

        return $this->planLabelMap($officeId)[$planNum] ?? 'No Insurance - 999999';
    }

    /** Gross/net production attributed to each payor for the period. @return array<int,array> */
    public function productionByPayor(MetricFilter $filter): array
    {
        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $filter->officeId)
            ->leftJoinSub($this->planForPatientSubquery($filter->officeId), 'cp', 'pl.PatNum', '=', 'cp.PatNum')
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereBetween('pl.ProcDate', [$filter->start, $filter->end]);
        if ($filter->clinics) {
            $q->whereIn('pl.ClinicNum', $filter->clinics);
        }

        return $q->selectRaw('COALESCE(cp.PlanNum, 0) AS plan_num, SUM(pl.ProcFee) AS gross, COUNT(*) AS procedures')
            ->groupBy('plan_num')
            ->orderByDesc('gross')
            ->get()
            ->map(fn ($r) => [
                'plan_num' => (int) $r->plan_num,
                'payor' => $this->payorLabel($r->plan_num, $filter->officeId),
                'gross' => round((float) $r->gross, 2),
                'procedures' => (int) $r->procedures,
            ])
            ->all();
    }

    /** @return array<int,string> PlanNum => label, cached for a day. */
    public function planLabelMap(?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        return Cache::remember("od_carrier_string_map_office_{$officeId}", 86400, function () use ($officeId) {
            try {
                $cMap = DB::table('od_carriers')
                    ->where('office_id', $officeId)
                    ->whereNotNull('CarrierName')
                    ->where('CarrierName', '!=', '')
                    ->pluck('CarrierName', 'CarrierNum')
                    ->toArray();

                $pMap = [];
                $plans = DB::table('od_insplans')
                    ->where('office_id', $officeId)
                    ->select('PlanNum', 'CarrierNum', 'GroupName')
                    ->get();

                foreach ($plans as $p) {
                    $cNum = (int) ($p->CarrierNum ?? 0);
                    $pMap[$p->PlanNum] = $cNum > 0 && isset($cMap[$cNum]) && ! in_array((int) $p->PlanNum, [13161])
                        ? $cMap[$cNum].' - '.$cNum
                        : 'No Insurance - 999999';
                }

                if (! empty($pMap)) {
                    return $pMap;
                }
            } catch (\Throwable $e) {
            }

            try {
                $client = app(OpenDentalClient::class);
                $cMap = [];
                foreach ($client->get('carriers?limit=5000') as $c) {
                    $name = trim($c['CarrierName'] ?? '');
                    if ($name !== '') {
                        $cMap[$c['CarrierNum']] = $name;
                    }
                }

                $pMap = [];
                foreach ($client->get('insplans?limit=5000') as $p) {
                    $cNum = $p['CarrierNum'] ?? 0;
                    $pMap[$p['PlanNum']] = $cNum > 0
                        ? ($cMap[$cNum] ?? 'Unknown Carrier').' - '.$cNum
                        : ($p['GroupName'] ?? 'Unknown Plan').' - Plan '.$p['PlanNum'];
                }

                return $pMap;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
