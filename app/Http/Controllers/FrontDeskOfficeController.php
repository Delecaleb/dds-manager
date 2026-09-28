<?php

namespace App\Http\Controllers;

use App\Domain\FrontDesk\FrontDeskFilter;
use App\Domain\FrontDesk\FrontDeskSource;
use App\Domain\FrontDesk\FrontDeskTaxonomy;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\Location;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Growth Engine — AI Front Desk, office level. {location} is a ClinicRegistry location
 * key, so an "office" here is exactly a location everywhere else in the app.
 *
 * List pages with a detail pane select their item by query string (?call=, ?thread=,
 * ?booking=, ?workflow=), so every selection is a shareable URL.
 */
class FrontDeskOfficeController extends Controller
{
    public function __construct(
        private readonly FrontDeskSource $source,
        private readonly ClinicRegistry $clinics,
    ) {}

    public function home(Request $request, string $location): View
    {
        $office = $this->location($location);
        $filter = FrontDeskFilter::fromRequest($request, [$office->key()]);

        return $this->page('home', $office, [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
        ]);
    }

    public function calls(Request $request, string $location): View
    {
        $office = $this->location($location);
        $calls = $this->source->calls($office);
        $selected = $request->filled('call')
            ? $this->source->call($office, (string) $request->query('call'))
            : ($calls[0] ?? null);

        return $this->page('calls', $office, [
            'calls' => $calls,
            'selected' => $selected,
            'agentNumber' => $this->source->settings($office)['phone-numbers']['agent'][0] ?? null,
        ]);
    }

    public function messages(Request $request, string $location): View
    {
        $office = $this->location($location);
        $threads = $this->source->conversations($office);
        $selected = $request->filled('thread')
            ? $this->source->conversation($office, (string) $request->query('thread'))
            : (isset($threads[0]) ? $this->source->conversation($office, $threads[0]['id']) : null);

        return $this->page('messages', $office, [
            'threads' => $threads,
            'selected' => $selected,
            'actions' => $this->source->messageActions($office),
        ]);
    }

    public function schedule(Request $request, string $location): View
    {
        $office = $this->location($location);
        $date = rescue(fn () => CarbonImmutable::parse((string) $request->query('date', 'today')), CarbonImmutable::today(), false);

        return $this->page('schedule', $office, [
            'date' => $date,
            'schedule' => $this->source->schedule($office, $date->toDateString()),
        ]);
    }

    public function analytics(Request $request, string $location): View
    {
        $office = $this->location($location);
        $filter = FrontDeskFilter::fromRequest($request, [$office->key()]);

        return $this->page('analytics', $office, [
            'filter' => $filter,
            'analytics' => $this->source->analytics($filter),
        ]);
    }

    public function workflows(Request $request, string $location): View
    {
        $office = $this->location($location);
        $workflows = $this->source->workflows($office);
        $selected = collect($workflows)->firstWhere('id', (string) $request->query('workflow')) ?? ($workflows[0] ?? null);

        return $this->page('workflows', $office, ['workflows' => $workflows, 'selected' => $selected]);
    }

    public function bookings(Request $request, string $location): View
    {
        $office = $this->location($location);
        $bookings = $this->source->bookings($office);
        $selected = $request->filled('booking')
            ? $this->source->booking($office, (string) $request->query('booking'))
            : ($bookings[0] ?? null);

        return $this->page('bookings', $office, ['bookings' => $bookings, 'selected' => $selected]);
    }

    public function settings(string $location, ?string $section = null): View
    {
        $office = $this->location($location);
        $section ??= array_key_first(FrontDeskTaxonomy::SETTINGS_SECTIONS);
        abort_unless(array_key_exists($section, FrontDeskTaxonomy::SETTINGS_SECTIONS), 404);

        return $this->page('settings', $office, [
            'section' => $section,
            'settings' => $this->source->settings($office)[$section],
        ]);
    }

    private function location(string $key): Location
    {
        return $this->clinics->locations()[$key] ?? abort(404);
    }

    private function page(string $view, Location $office, array $data): View
    {
        return view("marketing.front-desk.office.{$view}", ['office' => $office] + $data);
    }
}
