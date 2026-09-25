<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
use App\Models\OdClaimPayment;
use App\Models\OdDefinition;
use App\Models\OdPatient;
use App\Models\OdPayment;
use App\Models\OdProvider;
use App\Models\Office;
use App\Models\PaySplit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositSlipController extends Controller
{
    public function __construct(
        private readonly ClinicRegistry $clinics,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = $this->clinics->locations();
        $selectedLocations = $this->clinics->select(request('locations'))->keys();

        return view('deposit.index', compact('locations', 'selectedLocations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        if ($request->filled('locations')) {
            $locations = $this->clinics->select($request->input('locations'));
        } elseif ($request->filled('office_id') || $request->filled('clinic_num') || $request->filled('clinic_id')) {
            $officeInput = $request->input('office_id');
            if ($officeInput === 'all') {
                $locations = $this->clinics->select('all');
            } else {
                $officeId = ($officeInput !== null && $officeInput !== '') ? (int) $officeInput : Office::getActiveOfficeId();
                $clinicInput = $request->input('clinic_id') ?? $request->input('clinic_num');
                if ($clinicInput === null && $officeId !== null) {
                    $clinicInput = $this->clinics->getActiveClinicNum($officeId);
                }
                if ($clinicInput !== null && $clinicInput !== '' && $clinicInput !== 'all') {
                    $locations = $this->clinics->select("{$officeId}:{$clinicInput}");
                } elseif ($officeId !== null) {
                    $locations = $this->clinics->select((string) $officeId);
                } else {
                    $locations = $this->clinics->select(null);
                }
            }
        } else {
            $locations = $this->clinics->select(null);
        }

        $scopes = $locations->scopes();
        if (empty($scopes)) {
            return response()->json([
                'deposits' => [],
                'details' => [],
                'summary' => [
                    'total_amount' => 0.0,
                ],
            ]);
        }

        $paymentTable = (new OdPayment)->getTable();
        $defTable = (new OdDefinition)->getTable();
        $patientTable = (new OdPatient)->getTable();
        $claimPaymentTable = (new OdClaimPayment)->getTable();

        $applyScopes = function ($query, string $table = '') use ($scopes) {
            $prefix = $table !== '' ? "{$table}." : '';
            $query->where(function ($q) use ($scopes, $prefix) {
                foreach ($scopes as $officeId => $scopedClinics) {
                    $q->orWhere(function ($sub) use ($officeId, $scopedClinics, $prefix) {
                        $sub->where("{$prefix}office_id", $officeId);
                        if (! empty($scopedClinics)) {
                            $sub->whereIn("{$prefix}ClinicNum", $scopedClinics);
                        }
                    });
                }
            });
        };

        // ── 1. SUMMARY PAYMENTS QUERY ──
        $paymentsQuery = OdPayment::withoutGlobalScopes()
            ->leftJoin($defTable, function ($join) use ($paymentTable, $defTable) {
                $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                    ->on("{$paymentTable}.office_id", '=', "{$defTable}.office_id");
            })
            ->leftJoin(DB::raw("(SELECT DefNum, MIN(ItemName) as ItemName FROM {$defTable} WHERE Category = 10 OR ItemName IS NOT NULL GROUP BY DefNum) as def_fallback"), function ($join) use ($paymentTable) {
                $join->on("{$paymentTable}.PayType", '=', 'def_fallback.DefNum');
            })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end]);

        $applyScopes($paymentsQuery, $paymentTable);

        $payments = $paymentsQuery
            ->select(
                "{$paymentTable}.office_id",
                "{$paymentTable}.ClinicNum",
                DB::raw("COALESCE({$defTable}.ItemName, def_fallback.ItemName, 'Uncategorized Payment') as type"),
                DB::raw("SUM({$paymentTable}.PayAmt) as amount")
            )
            ->groupBy("{$paymentTable}.office_id", "{$paymentTable}.ClinicNum", DB::raw("COALESCE({$defTable}.ItemName, def_fallback.ItemName, 'Uncategorized Payment')"))
            ->get();

        $results = [];
        $totalAmount = 0.0;
        foreach ($payments as $p) {
            $loc = $this->clinics->locationFor((int) ($p->office_id ?? 1), (int) ($p->ClinicNum ?? 0))->name;
            $type = $p->type ?: 'Uncategorized Payment';
            $amt = (float) $p->amount;
            $totalAmount += $amt;

            $results[] = [
                'location' => $loc,
                'type' => $type,
                'amount' => $amt,
            ];
        }

        // ── 2. SUMMARY CLAIM PAYMENTS QUERY ──
        $claimPaymentsQuery = OdClaimPayment::withoutGlobalScopes()
            ->whereBetween('CheckDate', [$start, $end]);

        $applyScopes($claimPaymentsQuery);

        $claimPayments = $claimPaymentsQuery
            ->select(
                'office_id',
                'ClinicNum',
                DB::raw('SUM(CheckAmt) as amount')
            )
            ->groupBy('office_id', 'ClinicNum')
            ->get();

        foreach ($claimPayments as $cp) {
            $amt = (float) $cp->amount;
            $totalAmount += $amt;

            $results[] = [
                'location' => $this->clinics->locationFor((int) ($cp->office_id ?? 1), (int) ($cp->ClinicNum ?? 0))->name,
                'type' => 'Insurance Co Pmt',
                'amount' => $amt,
            ];
        }

        usort($results, function ($a, $b) {
            return strcmp($a['type'], $b['type']);
        });

        // ── 3. DETAILS TAB DATA ──
        $details = [];

        // Provider lookup: office_id => [ProvNum => Abbr]
        $providerMap = [];
        $provQuery = OdProvider::withoutGlobalScopes()->whereIn('office_id', array_keys($scopes));
        foreach ($provQuery->get(['office_id', 'ProvNum', 'Abbr']) as $pr) {
            $providerMap[$pr->office_id][$pr->ProvNum] = $pr->Abbr;
        }

        $paymentsForDetailsQuery = OdPayment::withoutGlobalScopes()
            ->leftJoin($defTable, function ($join) use ($paymentTable, $defTable) {
                $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                    ->on("{$paymentTable}.office_id", '=', "{$defTable}.office_id");
            })
            ->leftJoin(DB::raw("(SELECT DefNum, MIN(ItemName) as ItemName FROM {$defTable} WHERE Category = 10 OR ItemName IS NOT NULL GROUP BY DefNum) as def_fallback"), function ($join) use ($paymentTable) {
                $join->on("{$paymentTable}.PayType", '=', 'def_fallback.DefNum');
            })
            ->leftJoin($patientTable, function ($join) use ($paymentTable, $patientTable) {
                $join->on("{$paymentTable}.PatNum", '=', "{$patientTable}.PatNum")
                    ->on("{$paymentTable}.office_id", '=', "{$patientTable}.office_id");
            })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end]);

        $applyScopes($paymentsForDetailsQuery, $paymentTable);

        $paymentsForDetails = $paymentsForDetailsQuery
            ->select(
                "{$paymentTable}.PayNum",
                "{$paymentTable}.office_id",
                "{$paymentTable}.ClinicNum",
                DB::raw("COALESCE({$defTable}.ItemName, def_fallback.ItemName, 'Uncategorized Payment') as type"),
                "{$paymentTable}.PayAmt as amount",
                "{$paymentTable}.PayDate as date",
                "{$paymentTable}.PatNum",
                "{$patientTable}.FName",
                "{$patientTable}.LName",
                "{$paymentTable}.CheckNum",
                "{$paymentTable}.BankBranch"
            )
            ->orderBy("{$paymentTable}.PayDate", 'desc')
            ->get();

        // PaySplits lookup to associate doctor/provider per office
        $paySplitsProvMap = [];
        if ($paymentsForDetails->isNotEmpty()) {
            $splitsQuery = PaySplit::withoutGlobalScopes()->whereNotNull('ProvNum')->where('ProvNum', '>', 0);
            $officePayNums = $paymentsForDetails->groupBy('office_id')
                ->map(fn ($g) => $g->pluck('PayNum')->filter()->values()->all());

            $splitsQuery->where(function ($q) use ($officePayNums) {
                foreach ($officePayNums as $oId => $nums) {
                    if (! empty($nums)) {
                        $q->orWhere(function ($sub) use ($oId, $nums) {
                            $sub->where('office_id', $oId)->whereIn('PayNum', $nums);
                        });
                    }
                }
            });

            foreach ($splitsQuery->get(['office_id', 'PayNum', 'ProvNum']) as $split) {
                $key = "{$split->office_id}:{$split->PayNum}";
                $paySplitsProvMap[$key] = $split->ProvNum;
            }
        }

        foreach ($paymentsForDetails as $p) {
            $pOfficeId = (int) ($p->office_id ?? 1);
            $key = "{$pOfficeId}:{$p->PayNum}";
            $provNum = $paySplitsProvMap[$key] ?? null;
            $provAbbr = $provNum ? ($providerMap[$pOfficeId][$provNum] ?? '') : '';

            $details[] = [
                'office' => $this->clinics->locationFor($pOfficeId, (int) ($p->ClinicNum ?? 0))->name,
                'patient_name' => ($p->LName || $p->FName) ? trim($p->LName.', '.$p->FName, ', ') : '',
                'patient_id' => $p->PatNum,
                'provider' => $provAbbr,
                'provider_id' => $provNum ? (string) $provNum : '',
                'date' => $p->date,
                'payment_type' => $p->type ?: 'Uncategorized Payment',
                'type' => 'Patient Payment',
                'insurance' => '',
                'bank' => $p->BankBranch ?: '',
                'check_number' => $p->CheckNum ?: '',
                'unallocated' => '',
                'amount' => (float) $p->amount,
            ];
        }

        // ── 4. DETAILS CLAIM PAYMENTS QUERY ──
        $claimPaymentsForDetailsQuery = OdClaimPayment::withoutGlobalScopes()
            ->leftJoin($defTable, function ($join) use ($claimPaymentTable, $defTable) {
                $join->on("{$claimPaymentTable}.PayType", '=', "{$defTable}.DefNum")
                    ->on("{$claimPaymentTable}.office_id", '=', "{$defTable}.office_id");
            })
            ->leftJoin(DB::raw("(SELECT DefNum, MIN(ItemName) as ItemName FROM {$defTable} WHERE Category = 10 OR ItemName IS NOT NULL GROUP BY DefNum) as def_fallback"), function ($join) use ($claimPaymentTable) {
                $join->on("{$claimPaymentTable}.PayType", '=', 'def_fallback.DefNum');
            })
            ->whereBetween("{$claimPaymentTable}.CheckDate", [$start, $end]);

        $applyScopes($claimPaymentsForDetailsQuery, $claimPaymentTable);

        $claimPaymentsForDetails = $claimPaymentsForDetailsQuery
            ->select(
                "{$claimPaymentTable}.office_id",
                "{$claimPaymentTable}.ClinicNum",
                DB::raw("COALESCE({$defTable}.ItemName, def_fallback.ItemName, 'Insurance Co Pmt') as type"),
                "{$claimPaymentTable}.CheckAmt as amount",
                "{$claimPaymentTable}.CheckDate as date",
                "{$claimPaymentTable}.CarrierName",
                "{$claimPaymentTable}.BankBranch",
                "{$claimPaymentTable}.CheckNum"
            )
            ->orderBy("{$claimPaymentTable}.CheckDate", 'desc')
            ->get();

        foreach ($claimPaymentsForDetails as $cp) {
            $cpOfficeId = (int) ($cp->office_id ?? 1);
            $details[] = [
                'office' => $this->clinics->locationFor($cpOfficeId, (int) ($cp->ClinicNum ?? 0))->name,
                'patient_name' => '',
                'patient_id' => '',
                'provider' => '',
                'provider_id' => '',
                'date' => $cp->date,
                'payment_type' => $cp->type ?: 'Insurance Co Pmt',
                'type' => 'Insurance Co Pmt',
                'insurance' => $cp->CarrierName ?: '',
                'bank' => $cp->BankBranch ?: '',
                'check_number' => $cp->CheckNum ?: '',
                'unallocated' => '',
                'amount' => (float) $cp->amount,
            ];
        }

        return response()->json([
            'deposits' => $results,
            'details' => $details,
            'summary' => [
                'total_amount' => $totalAmount,
            ],
        ]);
    }
}
