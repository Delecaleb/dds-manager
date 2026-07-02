<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenDental\FinancialAnalyticsService;

class FinancialController extends Controller
{
    public function __construct(protected FinancialAnalyticsService $filterAnalysis)
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('financials.index');
    }

    public function getAnalytics(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $response = $this->filterAnalysis->filterAnalysis($startDate, $endDate);
        return json_encode($response);
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
}
