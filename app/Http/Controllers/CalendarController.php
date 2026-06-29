<?php

namespace App\Http\Controllers;


use App\Services\OpenDental\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{

public function index()
{
    return view('calendar.index');
}

public function getData(Request $request,CalendarService $calendar)
{
    $start = $request->get('start') ?? date('Y-m-d');
    $end = $request->get('end') ?? date('Y-m-d');

    Log::info("Fetching appointments from OpenDental API for date range: {$start} to {$end}");

    return response()->json(
        $calendar->events($start, $end)
    );


}

}