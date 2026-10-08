<?php

namespace App\View\Components;

use App\Domain\FrontDesk\FrontDeskSource;
use App\Domain\Marketing\MarketingFilter;
use App\Domain\Support\ClinicRegistry;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The Growth Engine shell: the marketing module runs as its own app inside Marcelo Analytics,
 * with a persistent left nav of its own instead of the analytics overlay menu.
 *
 * The Growth Engine holds three umbrellas — Marketing (website tracking, attribution, ad
 * campaigns, leads, alerts), AI Front Desk (calls, booking, intake) and AI Website
 * Builder. Each has its own nav; the hub at marketing.index links to all of them. The
 * umbrella is resolved from the route name, so pages never declare it themselves.
 *
 * Offices: a Growth Engine "office" is a reporting location exactly as the analytics
 * shell defines one (ClinicRegistry — an office, or one clinic of a multi-clinic office).
 * The header carries the same location picker, and the choice is shared through the
 * session, so switching office here switches it everywhere.
 */
class MarketingLayout extends Component
{
    /**
     * Umbrella definitions. Each nav entry is a route name, so adding a page is one line
     * here plus its route.
     *
     * @var array<string, array{label: string, short: string, icon: string, home: string, description: string, highlights: list<string>, status: ?string, nav: list<array{label: string, items: list<array{route: string, icon: string, label: string}>}>}>
     */
    public const UMBRELLAS = [
        'marketing' => [
            'label' => 'Marketing',
            'short' => 'Marketing',
            'icon' => 'megaphone',
            'home' => 'marketing.overview',
            'description' => 'Per office: website traffic and signups, where they came from, Google Ads spend and campaigns, the leads it produced, and what needs attention.',
            'highlights' => ['Websites', 'Attribution Funnel', 'Channels', 'Campaigns', 'Leads', 'Alerts'],
            'status' => null,
            'nav' => [
                [
                    'label' => 'Measure',
                    'items' => [
                        ['route' => 'marketing.overview', 'icon' => 'gauge', 'label' => 'Overview'],
                        ['route' => 'marketing.websites', 'icon' => 'globe', 'label' => 'Websites'],
                        ['route' => 'marketing.journeys', 'icon' => 'route', 'label' => 'Visitor Journeys'],
                        ['route' => 'marketing.funnel', 'icon' => 'filter', 'label' => 'Attribution Funnel'],
                        ['route' => 'marketing.channels', 'icon' => 'share-2', 'label' => 'Channels'],
                        ['route' => 'marketing.campaigns', 'icon' => 'megaphone', 'label' => 'Campaigns'],
                    ],
                ],
                [
                    'label' => 'Act',
                    'items' => [
                        ['route' => 'marketing.leads', 'icon' => 'users', 'label' => 'Leads'],
                        ['route' => 'marketing.alerts', 'icon' => 'bell-ring', 'label' => 'Alerts'],
                    ],
                ],
                [
                    'label' => 'Configure',
                    'items' => [
                        ['route' => 'marketing.tracking', 'icon' => 'code', 'label' => 'Tracking Script'],
                        ['route' => 'marketing.integrations', 'icon' => 'plug', 'label' => 'Integrations'],
                    ],
                ],
            ],
        ],
        'front-desk' => [
            'label' => 'AI Front Desk',
            'short' => 'Front Desk',
            'icon' => 'headset',
            'home' => 'marketing.front-desk.index',
            'description' => 'Turn calls into booked appointments: an AI receptionist that answers, books into OpenDental and takes intake.',
            'highlights' => ['Call Answering', 'Booking', 'Patient Intake', 'Call Analytics'],
            'status' => 'In build',
            'nav' => [
                [
                    'label' => 'Organization',
                    'items' => [
                        ['route' => 'marketing.front-desk.index', 'icon' => 'house', 'label' => 'Overview'],
                        ['route' => 'marketing.front-desk.analytics', 'icon' => 'chart-column', 'label' => 'Analytics'],
                        ['route' => 'marketing.front-desk.offices', 'icon' => 'map-pin', 'label' => 'Offices'],
                    ],
                ],
            ],
        ],
        'site-builder' => [
            'label' => 'AI Website Builder',
            'short' => 'Websites',
            'icon' => 'layout-template',
            'home' => 'marketing.site-builder.index',
            'description' => 'Describe the business, brand and pages; the AI writes an SEO-ready website you can preview, refine and download.',
            'highlights' => ['Brand & Logo', 'Custom Pages', 'SEO Keywords', 'Download .zip'],
            'status' => null,
            'nav' => [
                [
                    'label' => 'Websites',
                    'items' => [
                        ['route' => 'marketing.site-builder.index', 'icon' => 'layout-template', 'label' => 'All Websites', 'active' => ['marketing.site-builder.index', 'marketing.site-builder.show', 'marketing.site-builder.edit']],
                        ['route' => 'marketing.site-builder.create', 'icon' => 'sparkles', 'label' => 'New Website'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * AI Front Desk nav inside one office. Routes take the {location} parameter; `active`
     * is the route pattern that highlights the item (Settings covers all its sections).
     *
     * @var list<array{route: string, icon: string, label: string, active?: string}>
     */
    public const OFFICE_NAV = [
        ['route' => 'marketing.front-desk.office.home', 'icon' => 'house', 'label' => 'Home'],
        ['route' => 'marketing.front-desk.office.calls', 'icon' => 'phone', 'label' => 'Calls'],
        ['route' => 'marketing.front-desk.office.messages', 'icon' => 'message-square', 'label' => 'Messages'],
        ['route' => 'marketing.front-desk.office.schedule', 'icon' => 'calendar-days', 'label' => 'Schedule'],
        ['route' => 'marketing.front-desk.office.analytics', 'icon' => 'chart-column', 'label' => 'Analytics'],
        ['route' => 'marketing.front-desk.office.workflows', 'icon' => 'workflow', 'label' => 'Workflows'],
        ['route' => 'marketing.front-desk.office.bookings', 'icon' => 'calendar-check', 'label' => 'Online Bookings'],
        ['route' => 'marketing.front-desk.office.settings', 'icon' => 'settings', 'label' => 'Settings'],
    ];

    /** Marketing pages that are configuration, not a report: no period applies. */
    private const PAGES_WITHOUT_PERIOD = ['marketing.tracking', 'marketing.integrations'];

    /** Active umbrella key, or null on the Growth Engine hub. */
    public ?string $umbrella;

    public function __construct()
    {
        $this->umbrella = match (true) {
            request()->routeIs('marketing.index') => null,
            request()->routeIs('marketing.front-desk.*') => 'front-desk',
            request()->routeIs('marketing.site-builder.*') => 'site-builder',
            default => 'marketing',
        };
    }

    public function render(): View
    {
        $current = $this->umbrella !== null ? self::UMBRELLAS[$this->umbrella] : null;
        $office = null;
        $offices = [];
        $source = null;
        $clinics = app(ClinicRegistry::class);

        if ($this->umbrella === 'front-desk') {
            $offices = $clinics->locations();
            $key = request()->route('location');
            $office = is_string($key) ? ($offices[$key] ?? null) : null;
            $source = app(FrontDeskSource::class);

            if ($office !== null) {
                $current['nav'] = [[
                    'label' => $office->name,
                    'items' => array_map(fn (array $item) => $item + ['params' => ['location' => $office->key()]], self::OFFICE_NAV),
                ]];
            }
        }

        // The shared location picker: on every Marketing page and the Front Desk
        // organization pages. Inside one Front Desk office the office switcher takes over.
        $withLocations = $this->umbrella === 'marketing' || ($this->umbrella === 'front-desk' && $office === null);
        $withPeriod = $this->umbrella === 'marketing' && ! request()->routeIs(...self::PAGES_WITHOUT_PERIOD);
        $filter = $withLocations ? MarketingFilter::fromRequest(request(), $clinics, persist: false) : null;

        return view('layouts.marketing', [
            'umbrellas' => self::UMBRELLAS,
            'umbrellaKey' => $this->umbrella,
            'current' => $current,
            'office' => $office,
            'offices' => $offices,
            'frontDeskSource' => $source,
            'locations' => $withLocations ? $clinics->locations() : [],
            'filter' => $filter,
            'withLocations' => $withLocations,
            'withPeriod' => $withPeriod,
            // Nav links carry the period so moving between report pages keeps the dates;
            // the location choice is already in the session.
            'navQuery' => $withPeriod ? ['start_date' => $filter->start, 'end_date' => $filter->end] : [],
        ]);
    }
}
