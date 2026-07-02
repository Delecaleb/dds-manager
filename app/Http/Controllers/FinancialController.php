<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\FinancialAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancialController extends Controller
{
    public function __construct(protected FinancialAnalyticsService $analytics) {}

    public function index()
    {
        return view('financials.index');
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end   = $request->input('end_date',   now()->toDateString());
        Log::info("FinancialController::data called with start_date={$start} and end_date={$end}");
        return response()->json(
            $this->analytics->filterAnalysis($start, $end)
        );
    }
}
