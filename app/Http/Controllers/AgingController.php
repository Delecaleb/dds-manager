<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\AgingCalculationService;
use Illuminate\Http\Request;

class AgingController extends Controller
{
    public function __construct(
        protected AgingCalculationService $agingCalculationService
    ) {
    }

    public function index()
    {
        return view('aging.index');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 20);
        $search = $request->input('search.value') ?: null;
        $mode   = $request->get('mode', 'responsible_party');

        if ($mode === 'by_patient') {
            return $this->byPatientData($request, $draw, $start, $length, $search);
        }

        return $this->responsiblePartyData($request, $draw, $start, $length, $search);
    }

    /**
     * Guarantor-level AR aging, computed by AgingCalculationService from
     * real ledger transactions rather than OpenDental's own coarse 4-bucket
     * balance snapshot. Also serves as the fallback for by_office/
     * by_insurance until those tabs get dedicated grouping.
     */
    private function responsiblePartyData(Request $request, int $draw, int $start, int $length, ?string $search)
    {
        $asOfDate = $request->get('as_of_date', now()->toDateString());
        $includeCredits = $request->get('credits', 'include') !== 'exclude';

        $result = $this->agingCalculationService->guarantorAging(
            $asOfDate,
            $search,
            $includeCredits,
            $start,
            $length
        );

        $fmt = fn ($v) => '$ ' . number_format((float) ($v ?? 0), 2);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $result['totalRecords'],
            'recordsFiltered' => $result['filteredRecords'],
            'data'            => $result['data']->map(fn ($r) => [
                'guarantor_id'   => $r->guarantor_id,
                'patient_id'     => $r->guarantor_id,
                'guarantor_name' => $r->guarantor_name,
                'family_names'   => $r->family_names ?: $r->guarantor_name,
                'family_ids'     => $r->family_ids ?: (string) $r->guarantor_id,
                'office'         => $r->office,
                'bal_current'    => $fmt($r->bal_current),
                'bal_30'         => $fmt($r->bal_30),
                'bal_60'         => $fmt($r->bal_60),
                'bal_90'         => $fmt($r->bal_90),
                'bal_120'        => $fmt($r->bal_120),
                'bal_180'        => $fmt($r->bal_180),
                'bal_240'        => $fmt($r->bal_240),
                'bal_365'        => $fmt($r->bal_365),
                'credit_balance' => $fmt($r->credit_balance),
                'contract'       => $fmt($r->contract),
                'total'          => $fmt($r->total),
            ]),
            'totals' => [
                'current'      => $fmt($result['totals']['current_total']),
                'thirty'       => $fmt($result['totals']['thirty_total']),
                'sixty'        => $fmt($result['totals']['sixty_total']),
                'ninety'       => $fmt($result['totals']['ninety_total']),
                'onetwenty'    => $fmt($result['totals']['onetwenty_total']),
                'oneeighty'    => $fmt($result['totals']['oneeighty_total']),
                'twofourty'    => $fmt($result['totals']['twofourty_total']),
                'threesixfive' => $fmt($result['totals']['threesixfive_total']),
                'credit'       => $fmt($result['totals']['credit_total']),
                'contract'     => $fmt($result['totals']['contract_total']),
                'grand'        => $fmt($result['totals']['grand_total']),
            ],
        ]);
    }

    /**
     * Individual-patient AR aging via the same ledger engine as the
     * Responsible Party tab, grouped per patient instead of per guarantor.
     * Same 16-column shape as Responsible Party — Jarvis's By Patient tab
     * uses the identical layout, just grouped at patient granularity.
     * Previously this read OpenDental's own per-patient Bal_0_30/.../
     * BalTotal fields directly, but those are unpopulated in this
     * environment (never synced by OpenDental), so that query always
     * returned zero rows.
     */
    private function byPatientData(Request $request, int $draw, int $start, int $length, ?string $search)
    {
        $asOfDate = $request->get('as_of_date', now()->toDateString());
        $includeCredits = $request->get('credits', 'include') !== 'exclude';

        $result = $this->agingCalculationService->patientAging(
            $asOfDate,
            $search,
            $includeCredits,
            $start,
            $length
        );

        $fmt = fn ($v) => '$ ' . number_format((float) ($v ?? 0), 2);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $result['totalRecords'],
            'recordsFiltered' => $result['filteredRecords'],
            'data'            => $result['data']->map(fn ($r) => [
                'guarantor_id'   => $r->guarantor_id,
                'patient_id'     => $r->patient_id,
                'guarantor_name' => $r->guarantor_name,
                'family_names'   => $r->family_names,
                'family_ids'     => $r->family_ids,
                'office'         => $r->office,
                'bal_current'    => $fmt($r->bal_current),
                'bal_30'         => $fmt($r->bal_30),
                'bal_60'         => $fmt($r->bal_60),
                'bal_90'         => $fmt($r->bal_90),
                'bal_120'        => $fmt($r->bal_120),
                'bal_180'        => $fmt($r->bal_180),
                'bal_240'        => $fmt($r->bal_240),
                'bal_365'        => $fmt($r->bal_365),
                'credit_balance' => $fmt($r->credit_balance),
                'contract'       => $fmt($r->contract),
                'total'          => $fmt($r->total),
            ]),
            'totals' => [
                'current'      => $fmt($result['totals']['current_total']),
                'thirty'       => $fmt($result['totals']['thirty_total']),
                'sixty'        => $fmt($result['totals']['sixty_total']),
                'ninety'       => $fmt($result['totals']['ninety_total']),
                'onetwenty'    => $fmt($result['totals']['onetwenty_total']),
                'oneeighty'    => $fmt($result['totals']['oneeighty_total']),
                'twofourty'    => $fmt($result['totals']['twofourty_total']),
                'threesixfive' => $fmt($result['totals']['threesixfive_total']),
                'credit'       => $fmt($result['totals']['credit_total']),
                'contract'     => $fmt($result['totals']['contract_total']),
                'grand'        => $fmt($result['totals']['grand_total']),
            ],
        ]);
    }
}
