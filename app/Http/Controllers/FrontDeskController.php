<?php

namespace App\Http\Controllers;

use App\Domain\FrontDesk\FrontDeskFilter;
use App\Domain\FrontDesk\FrontDeskSource;
use App\Domain\Support\ClinicRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Growth Engine — AI Front Desk, organization level: every office together.
 * Office-level pages live in FrontDeskOfficeController.
 */
class FrontDeskController extends Controller
{
    public function __construct(private readonly FrontDeskSource $source) {}

    public function index(Request $request): View
    {
        $filter = FrontDeskFilter::fromRequest($request);

        return view('marketing.front-desk.overview', [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
            'offices' => $this->source->offices($filter),
        ]);
    }

    public function analytics(Request $request, ClinicRegistry $clinics): View
    {
        $all = $clinics->locations();
        $office = (string) $request->query('office', '');
        $filter = FrontDeskFilter::fromRequest($request, isset($all[$office]) ? [$office] : []);

        return view('marketing.front-desk.analytics', [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
            'locations' => $all,
            'office' => isset($all[$office]) ? $office : null,
        ]);
    }

    public function offices(Request $request): View
    {
        $filter = FrontDeskFilter::fromRequest($request);

        return view('marketing.front-desk.offices', [
            'offices' => $this->source->offices($filter),
        ]);
    }
}
