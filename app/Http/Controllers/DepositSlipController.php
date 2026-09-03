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
        return view('deposit.index');
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
        $officeId = Office::getActiveOfficeId();

        $paymentTable = (new OdPayment)->getTable();
        $defTable = (new OdDefinition)->getTable();
        $patientTable = (new OdPatient)->getTable();

        // Basic query to group payments by PayType in this date range.
        $payments = OdPayment::leftJoin($defTable, function ($join) use ($paymentTable, $defTable, $officeId) {
            $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                ->where("{$defTable}.office_id", '=', $officeId);
        })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end])
            ->select("{$paymentTable}.ClinicNum", "{$defTable}.ItemName as type", DB::raw("SUM({$paymentTable}.PayAmt) as amount"))
            ->groupBy("{$paymentTable}.ClinicNum", "{$defTable}.ItemName")
            ->get();

        $results = [];
        $totalAmount = 0;
        foreach ($payments as $p) {
            $loc = $this->clinics->name((int) ($p->ClinicNum ?? 0), $officeId);
            $type = $p->type ?: 'Uncategorized Payment';
            $amt = (float) $p->amount;
            $totalAmount += $amt;

            $results[] = [
                'location' => $loc,
                'type' => $type,
                'amount' => $amt,
            ];
        }

        // Include claim payments to match insurance
        $claimPayments = OdClaimPayment::whereBetween('CheckDate', [$start, $end])
            ->select('ClinicNum', DB::raw('SUM(CheckAmt) as amount'))
            ->groupBy('ClinicNum')
            ->get();

        foreach ($claimPayments as $cp) {
            $amt = (float) $cp->amount;
            $totalAmount += $amt;

            $results[] = [
                'location' => $this->clinics->name((int) ($cp->ClinicNum ?? 0), $officeId),
                'type' => 'Insurance Co Pmt',
                'amount' => $amt,
            ];
        }

        usort($results, function ($a, $b) {
            return strcmp($a['type'], $b['type']);
        });

        // ── DETAILS TAB DATA ──
        $details = [];
        $claimPaymentTable = (new OdClaimPayment)->getTable();
        $providers = OdProvider::pluck('Abbr', 'ProvNum');

        $paymentsForDetails = OdPayment::leftJoin($defTable, function ($join) use ($paymentTable, $defTable, $officeId) {
            $join->on("{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
                ->where("{$defTable}.office_id", '=', $officeId);
        })
            ->leftJoin($patientTable, function ($join) use ($paymentTable, $patientTable, $officeId) {
                $join->on("{$paymentTable}.PatNum", '=', "{$patientTable}.PatNum")
                    ->where("{$patientTable}.office_id", '=', $officeId);
            })
            ->whereBetween("{$paymentTable}.PayDate", [$start, $end])
            ->select(
                "{$paymentTable}.PayNum",
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

        $payNums = $paymentsForDetails->pluck('PayNum')->filter()->values();
        $paySplitsProvMap = [];
        if ($payNums->isNotEmpty()) {
            $paySplitsProvMap = PaySplit::whereIn('PayNum', $payNums)
                ->whereNotNull('ProvNum')
                ->where('ProvNum', '>', 0)
                ->pluck('ProvNum', 'PayNum')
                ->toArray();
        }

        foreach ($paymentsForDetails as $p) {
            $provNum = $paySplitsProvMap[$p->PayNum] ?? null;
            $provAbbr = $provNum ? ($providers[$provNum] ?? '') : '';

            $details[] = [
                'office' => $this->clinics->name((int) ($p->ClinicNum ?? 0), $officeId),
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

        // Include insurance claim payments in details tab
        $claimPaymentsForDetails = OdClaimPayment::leftJoin($defTable, function ($join) use ($claimPaymentTable, $defTable, $officeId) {
            $join->on("{$claimPaymentTable}.PayType", '=', "{$defTable}.DefNum")
                ->where("{$defTable}.office_id", '=', $officeId);
        })
            ->whereBetween("{$claimPaymentTable}.CheckDate", [$start, $end])
            ->select(
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
            $details[] = [
                'office' => $this->clinics->name((int) ($cp->ClinicNum ?? 0), $officeId),
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
