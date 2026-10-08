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
    public function __construct(
        private readonly FrontDeskSource $source,
        private readonly ClinicRegistry $clinics,
    ) {}

    public function index(Request $request): View
    {
        $filter = FrontDeskFilter::fromRequest($request, $this->selectedKeys($request));

        return view('marketing.front-desk.overview', [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
            'offices' => $this->source->offices($filter),
        ]);
    }

    public function analytics(Request $request): View
    {
        $filter = FrontDeskFilter::fromRequest($request, $this->selectedKeys($request));

        return view('marketing.front-desk.analytics', [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
        ]);
    }

    public function offices(Request $request): View
    {
        $filter = FrontDeskFilter::fromRequest($request, $this->selectedKeys($request));

        return view('marketing.front-desk.offices', [
            'offices' => $this->source->offices($filter),
        ]);
    }

    /**
     * The locations picked in the shared picker (session-persisted, like the analytics
     * shell). Every location selected = no filter, so unassigned data is never hidden.
     *
     * @return list<string>
     */
    private function selectedKeys(Request $request): array
    {
        $selection = $this->clinics->select($request->input('locations'));

        return count($selection->locations()) >= count($this->clinics->locations()) ? [] : $selection->keys();
    }
}
