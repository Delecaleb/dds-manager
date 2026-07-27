<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
use Illuminate\Http\Request;

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

    // public function data(Request $request)
    // {
    //     $start = $request->input('start_date', now()->startOfMonth()->toDateString());
    //     $end = $request->input('end_date', now()->toDateString());

    //     $paymentTable = (new \App\Models\OdPayment)->getTable();
    //     $defTable = (new \App\Models\OdDefinition)->getTable();
    //     $patientTable = (new \App\Models\OdPatient)->getTable();

    //     // Basic query to group payments by PayType in this date range. 
    //     $payments = \App\Models\OdPayment::leftJoin($defTable, "{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
    //         ->whereBetween("{$paymentTable}.PayDate", [$start, $end])
    //         ->select("{$paymentTable}.ClinicNum", "{$defTable}.ItemName as type", \Illuminate\Support\Facades\DB::raw("SUM({$paymentTable}.PayAmt) as amount"))
    //         ->groupBy("{$paymentTable}.ClinicNum", "{$defTable}.ItemName")
    //         ->get();

    //     $results = [];
    //     $totalAmount = 0;
    //     foreach ($payments as $p) {
    //         $loc = $this->clinics->name((int) ($p->ClinicNum ?? 0));
    //         $type = $p->type ?: 'Uncategorized Payment';
    //         $amt = (float) $p->amount;
    //         $totalAmount += $amt;

    //         $results[] = [
    //             'location' => $loc,
    //             'type' => $type,
    //             'amount' => $amt
    //         ];
    //     }

    //     // Include claim payments to match insurance
    //     $claimPayments = \App\Models\OdClaimPayment::whereBetween('CheckDate', [$start, $end])
    //         ->select('ClinicNum', \Illuminate\Support\Facades\DB::raw('SUM(CheckAmt) as amount'))
    //         ->groupBy('ClinicNum')
    //         ->get();

    //     foreach ($claimPayments as $cp) {
    //         $amt = (float) $cp->amount;
    //         $totalAmount += $amt;

    //         $results[] = [
    //             'location' => $this->clinics->name((int) ($cp->ClinicNum ?? 0)),
    //             'type' => 'Insurance Co Pmt',
    //             'amount' => $amt
    //         ];
    //     }

    //     usort($results, function ($a, $b) {
    //         return strcmp($a['type'], $b['type']);
    //     });

    //     // ── DETAILS TAB DATA ──
    //     $details = [];
    //     $providers = \App\Models\OdProvider::pluck('Abbr', 'ProvNum');

    //     $paymentsForDetails = \App\Models\OdPayment::leftJoin($defTable, "{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
    //         ->leftJoin($patientTable, "{$paymentTable}.PatNum", '=', "{$patientTable}.PatNum")
    //         ->whereBetween("{$paymentTable}.PayDate", [$start, $end])
    //         ->select(
    //             "{$paymentTable}.ClinicNum",
    //             "{$defTable}.ItemName as type",
    //             "{$paymentTable}.PayAmt as amount",
    //             "{$paymentTable}.PayDate as date",
    //             "{$paymentTable}.PatNum",
    //             "{$patientTable}.FName",
    //             "{$patientTable}.LName",
    //             "{$paymentTable}.CheckNum"
    //         )
    //         ->orderBy("{$paymentTable}.PayDate", 'desc')
    //         ->limit(100)
    //         ->get();

    //     foreach ($paymentsForDetails as $p) {
    //         $details[] = [
    //             'office' => $this->clinics->name((int) ($p->ClinicNum ?? 0)),
    //             'patient_name' => ($p->LName || $p->FName) ? trim($p->LName . ', ' . $p->FName, ', ') : '',
    //             'patient_id' => $p->PatNum,
    //             'provider' => '',
    //             'provider_id' => '',
    //             'date' => $p->date,
    //             'payment_type' => $p->type,
    //             'type' => 'Patient Payment',
    //             'insurance' => '',
    //             'bank' => '',
    //             'check_number' => $p->CheckNum,
    //             'unallocated' => '',
    //             'amount' => (float) $p->amount
    //         ];
    //     }

    //     return response()->json([
    //         'deposits' => $results,
    //         'details' => $details,
    //         'summary' => [
    //             'total_amount' => $totalAmount,
    //         ]
    //     ]);
    // }


    public function data(Request $request)
{
    $start = $request->input('start_date', now()->startOfMonth()->toDateString());
    $end = $request->input('end_date', now()->toDateString());

    $paymentTable = (new \App\Models\OdPayment)->getTable();
    $defTable = (new \App\Models\OdDefinition)->getTable();
    $patientTable = (new \App\Models\OdPatient)->getTable();
    $providerTable = (new \App\Models\OdProvider)->getTable();
    $splitTable = (new \App\Models\PaySplit)->getTable();
    $bankTable = (new \App\Models\OdBank)->getTable(); // if exists

    // Your existing summary query is fine...
    // But consider adding provider and bank grouping for more detail

    // DETAILS QUERY - Full version
    $paymentsForDetails = \App\Models\OdPayment::leftJoin($defTable, "{$paymentTable}.PayType", '=', "{$defTable}.DefNum")
        ->leftJoin($patientTable, "{$paymentTable}.PatNum", '=', "{$patientTable}.PatNum")
        ->leftJoin($providerTable, "{$paymentTable}.ProvNum", '=', "{$providerTable}.ProvNum")
        ->leftJoin($bankTable, "{$paymentTable}.BankNum", '=', "{$bankTable}.BankNum")
        ->leftJoin($splitTable, function($join) use ($paymentTable, $splitTable) {
            $join->on("{$paymentTable}.PayNum", '=', "{$splitTable}.PayNum")
                 ->whereNull("{$splitTable}.ProcNum");
        })
        ->whereBetween("{$paymentTable}.PayDate", [$start, $end])
        ->select(
            "{$paymentTable}.ClinicNum",
            "{$defTable}.ItemName as payment_type_desc",
            "{$paymentTable}.PayAmt as amount",
            "{$paymentTable}.PayDate as date",
            "{$paymentTable}.PatNum",
            "{$patientTable}.FName",
            "{$patientTable}.LName",
            "{$paymentTable}.CheckNum",
            "{$providerTable}.FName as ProvFName",
            "{$providerTable}.LName as ProvLName",
            "{$providerTable}.Abbr as ProvAbbr",
            "{$bankTable}.BankName as bank_name",
            \Illuminate\Support\Facades\DB::raw("COALESCE(SUM({$splitTable}.SplitAmt), 0) as unallocated")
        )
        ->groupBy(
            "{$paymentTable}.ClinicNum",
            "{$defTable}.ItemName",
            "{$paymentTable}.PayAmt",
            "{$paymentTable}.PayDate",
            "{$paymentTable}.PatNum",
            "{$patientTable}.FName",
            "{$patientTable}.LName",
            "{$paymentTable}.CheckNum",
            "{$providerTable}.FName",
            "{$providerTable}.LName",
            "{$providerTable}.Abbr",
            "{$bankTable}.BankName"
        )
        ->orderBy("{$paymentTable}.PayDate", 'desc')
        ->limit(100)
        ->get();

    // Then map to your details array with all fields populated
    $details = [];
    foreach ($paymentsForDetails as $p) {
        $details[] = [
            'office' => $this->clinics->name((int) ($p->ClinicNum ?? 0)),
            'patient_name' => trim($p->LName . ', ' . $p->FName, ', '),
            'patient_id' => $p->PatNum,
            'provider' => trim($p->ProvLName . ', ' . $p->ProvFName, ', '),
            'provider_id' => $p->ProvAbbr ? $p->ProvNum . ' - ' . $p->ProvAbbr : '',
            'date' => $p->date,
            'payment_type' => $p->payment_type_desc,
            'type' => 'Patient Payment', // or derive from payment type
            'insurance' => '', // join insplan if needed
            'bank' => $p->bank_name ?? 'AR',
            'check_number' => $p->CheckNum ?? '0000',
            'unallocated' => '$ ' . number_format($p->unallocated, 2),
            'amount' => '$ ' . number_format($p->amount, 2)
        ];
    }

    return response()->json([
        'deposits' => $results,
        'details' => $details,
        'summary' => [
            'total_amount' => $totalAmount,
        ]
    ]);
}
}
