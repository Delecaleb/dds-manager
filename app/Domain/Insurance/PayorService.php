<?php

namespace App\Domain\Insurance;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
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
     * The patient -> plan map: each patient's highest PlanNum from claim procs (D10).
     * Join against this (leftJoinSub) to attribute a patient's activity to a payor.
     */
    public function planForPatientSubquery(): Builder
    {
        return DB::table('od_claim_procs')
            ->select('PatNum', DB::raw('MAX(PlanNum) as PlanNum'))
            ->groupBy('PatNum');
    }

    /** Display label for a plan (carrier name - carrier num), resolved via the OpenDental API. */
    public function payorLabel(int|string $planNum): string
    {
        $planNum = (int) $planNum;
        if ($planNum === 0) {
            return 'No Insurance - 999999';
        }

        return $this->planLabelMap()[$planNum] ?? 'Plan '.$planNum;
    }

    /** Gross/net production attributed to each payor for the period. @return array<int,array> */
    public function productionByPayor(MetricFilter $filter): array
    {
        $q = DB::table('od_procedure_logs as pl')
            ->leftJoinSub($this->planForPatientSubquery(), 'cp', 'pl.PatNum', '=', 'cp.PatNum')
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
                'payor' => $this->payorLabel($r->plan_num),
                'gross' => round((float) $r->gross, 2),
                'procedures' => (int) $r->procedures,
            ])
            ->all();
    }

    /** @return array<int,string> PlanNum => label, cached for a day. */
    public function planLabelMap(): array
    {
        return Cache::remember('od_carrier_string_map', 86400, function () {
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

                if (! empty($pMap)) {
                    return $pMap;
                }
            } catch (\Throwable $e) {
                // Fall back to database tables if API call fails
            }

            try {
                $cMap = DB::table('od_carriers')
                    ->whereNotNull('CarrierName')
                    ->where('CarrierName', '!=', '')
                    ->pluck('CarrierName', 'CarrierNum')
                    ->toArray();

                $pMap = [];
                $plans = DB::table('od_insplans')
                    ->select('PlanNum', 'CarrierNum', 'GroupName')
                    ->get();

                foreach ($plans as $p) {
                    $cNum = (int) ($p->CarrierNum ?? 0);
                    $pMap[$p->PlanNum] = $cNum > 0
                        ? ($cMap[$cNum] ?? 'Unknown Carrier').' - '.$cNum
                        : ($p->GroupName ? $p->GroupName.' - Plan '.$p->PlanNum : 'Plan '.$p->PlanNum);
                }

                return $pMap;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
