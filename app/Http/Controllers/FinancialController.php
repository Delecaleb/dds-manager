<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\FinancialAnalyticsService;
use App\Services\OpenDental\PatientAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancialController extends Controller
{
    public function __construct(protected FinancialAnalyticsService $financialAnalytics, protected PatientAnalyticsService $patientAnalytics)
    {
    }

    public function index()
    {
        return view('financials.index');
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $visitsData = $this->patientAnalytics->getPatientAnalytics($start, $end);
        $financialData = $this->financialAnalytics->filterAnalysis($start, $end);
        return response()->json(
            array_merge($visitsData, $financialData)
        );
    }
}
