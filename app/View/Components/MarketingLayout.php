<?php

namespace App\View\Components;

use App\Domain\FrontDesk\FrontDeskSource;
use App\Domain\Support\ClinicRegistry;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The Growth Engine shell: the marketing module runs as its own app inside Marcelo Analytics,
 * with a persistent left nav of its own instead of the analytics overlay menu.
 *
 * The Growth Engine holds two umbrellas — Marketing (demand: websites, attribution,
 * campaigns, leads) and AI Front Desk (conversion: calls, booking, intake). Each has its
 * own nav; the hub at marketing.index links to both. The umbrella is resolved from the
 * route name, so pages never declare it themselves.
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
            'description' => 'Bring patients in: website traffic, attribution, campaigns and leads, traced through to completed production.',
            'highlights' => ['Websites', 'Attribution Funnel', 'Channels', 'Campaigns', 'Leads', 'Automations'],
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
                        ['route' => 'marketing.automations', 'icon' => 'workflow', 'label' => 'Automations'],
                        ['route' => 'marketing.alerts', 'icon' => 'bell-ring', 'label' => 'Alerts'],
                    ],
                ],
                [
                    'label' => 'Configure',
                    'items' => [
                        ['route' => 'marketing.tracking', 'icon' => 'code', 'label' => 'Tracking Script'],
                        ['route' => 'marketing.integrations', 'icon' => 'plug', 'label' => 'Integrations'],
                        ['route' => 'marketing.settings', 'icon' => 'sliders', 'label' => 'Settings'],
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

        if ($this->umbrella === 'front-desk') {
            $clinics = app(ClinicRegistry::class);
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

        return view('layouts.marketing', [
            'umbrellas' => self::UMBRELLAS,
            'umbrellaKey' => $this->umbrella,
            'current' => $current,
            'office' => $office,
            'offices' => $offices,
            'frontDeskSource' => $source,
        ]);
    }
}
