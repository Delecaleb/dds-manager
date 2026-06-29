<?php

namespace App\Http\Controllers;


use App\Services\OpenDental\DashboardAnalyticsService;



class DashboardController extends Controller
{


public function index(
    DashboardAnalyticsService $dashboard
)
{


return view('dashboard', ['data'=>$dashboard->metrics()]);

}


}