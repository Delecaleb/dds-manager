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
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $activeOfficeId = Office::getActiveOfficeId();
        $clinics = $this->clinics->all($activeOfficeId);

        return view('deposit.index', compact('offices', 'activeOfficeId', 'clinics'));
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

        $officeInput = $request->input('office_id');
        $officeId = ($officeInput !== null && $officeInput !== '' && $officeInput !== 'all')
            ? (int) $officeInput
            : ($officeInput === 'all' ? null : Office::getActiveOfficeId());

        $clinicNum = ($request->filled('clinic_num') && $request->input('clinic_num') !== 'all')
            ? (int) $request->input('clinic_num')
            : null;

        $paymentTable = (new OdPayment)->getTable();
        $defTable = (new OdDefinition)->getTable();
        $patientTable = (new OdPatient)->getTable();
        $claimPaymentTable = (new OdClaimPayment)->getTable();

        // ── 1. SUMMARY PAYMENTS QUERY ──
        $paymentsQuery = OdPayment::withoutGlobalScopes()
            ->leftJoin($defTable, function ($join) use ($paymentTable, $defTable) {
                $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                    ->on("{$paymentTable}.office_id", '=', "{$defTable}.office_id");
            })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end]);

        if ($officeId !== null) {
            $paymentsQuery->where("{$paymentTable}.office_id", $officeId);
        }
        if ($clinicNum !== null) {
            $paymentsQuery->where("{$paymentTable}.ClinicNum", $clinicNum);
        }

        $payments = $paymentsQuery
            ->select(
                "{$paymentTable}.office_id",
                "{$paymentTable}.ClinicNum",
                "{$defTable}.ItemName as type",
                DB::raw("SUM({$paymentTable}.PayAmt) as amount")
            )
            ->groupBy("{$paymentTable}.office_id", "{$paymentTable}.ClinicNum", "{$defTable}.ItemName")
            ->get();

        $results = [];
        $totalAmount = 0;
        foreach ($payments as $p) {
            $loc = $this->clinics->name((int) ($p->ClinicNum ?? 0), (int) ($p->office_id ?? $officeId ?? 1));
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

        if ($officeId !== null) {
            $claimPaymentsQuery->where('office_id', $officeId);
        }
        if ($clinicNum !== null) {
            $claimPaymentsQuery->where('ClinicNum', $clinicNum);
        }

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
                'location' => $this->clinics->name((int) ($cp->ClinicNum ?? 0), (int) ($cp->office_id ?? $officeId ?? 1)),
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
        $provQuery = OdProvider::withoutGlobalScopes();
        if ($officeId !== null) {
            $provQuery->where('office_id', $officeId);
        }
        foreach ($provQuery->get(['office_id', 'ProvNum', 'Abbr']) as $pr) {
            $providerMap[$pr->office_id][$pr->ProvNum] = $pr->Abbr;
        }

        $paymentsForDetailsQuery = OdPayment::withoutGlobalScopes()
            ->leftJoin($defTable, function ($join) use ($paymentTable, $defTable) {
                $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                    ->on("{$paymentTable}.office_id", '=', "{$defTable}.office_id");
            })
            ->leftJoin($patientTable, function ($join) use ($paymentTable, $patientTable) {
                $join->on("{$paymentTable}.PatNum", '=', "{$patientTable}.PatNum")
                    ->on("{$paymentTable}.office_id", '=', "{$patientTable}.office_id");
            })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end]);

        if ($officeId !== null) {
            $paymentsForDetailsQuery->where("{$paymentTable}.office_id", $officeId);
        }
        if ($clinicNum !== null) {
            $paymentsForDetailsQuery->where("{$paymentTable}.ClinicNum", $clinicNum);
        }

        $paymentsForDetails = $paymentsForDetailsQuery
            ->select(
                "{$paymentTable}.PayNum",
                "{$paymentTable}.office_id",
                "{$paymentTable}.ClinicNum",
                "{$defTable}.ItemName as type",
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
            if ($officeId !== null) {
                $splitsQuery->where('office_id', $officeId);
                $payNums = $paymentsForDetails->pluck('PayNum')->filter()->values()->all();
                if (! empty($payNums)) {
                    $splitsQuery->whereIn('PayNum', $payNums);
                }
            } else {
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
            }

            foreach ($splitsQuery->get(['office_id', 'PayNum', 'ProvNum']) as $split) {
                $key = "{$split->office_id}:{$split->PayNum}";
                $paySplitsProvMap[$key] = $split->ProvNum;
            }
        }

        foreach ($paymentsForDetails as $p) {
            $pOfficeId = (int) ($p->office_id ?? $officeId ?? 1);
            $key = "{$pOfficeId}:{$p->PayNum}";
            $provNum = $paySplitsProvMap[$key] ?? null;
            $provAbbr = $provNum ? ($providerMap[$pOfficeId][$provNum] ?? '') : '';

            $details[] = [
                'office' => $this->clinics->name((int) ($p->ClinicNum ?? 0), $pOfficeId),
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
            ->whereBetween("{$claimPaymentTable}.CheckDate", [$start, $end]);

        if ($officeId !== null) {
            $claimPaymentsForDetailsQuery->where("{$claimPaymentTable}.office_id", $officeId);
        }
        if ($clinicNum !== null) {
            $claimPaymentsForDetailsQuery->where("{$claimPaymentTable}.ClinicNum", $clinicNum);
        }

        $claimPaymentsForDetails = $claimPaymentsForDetailsQuery
            ->select(
                "{$claimPaymentTable}.office_id",
                "{$claimPaymentTable}.ClinicNum",
                "{$defTable}.ItemName as type",
                "{$claimPaymentTable}.CheckAmt as amount",
                "{$claimPaymentTable}.CheckDate as date",
                "{$claimPaymentTable}.CarrierName",
                "{$claimPaymentTable}.BankBranch",
                "{$claimPaymentTable}.CheckNum"
            )
            ->orderBy("{$claimPaymentTable}.CheckDate", 'desc')
            ->get();

        foreach ($claimPaymentsForDetails as $cp) {
            $cpOfficeId = (int) ($cp->office_id ?? $officeId ?? 1);
            $details[] = [
                'office' => $this->clinics->name((int) ($cp->ClinicNum ?? 0), $cpOfficeId),
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
