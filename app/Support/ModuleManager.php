<?php

namespace App\Support;

use Illuminate\Support\Str;

class ModuleManager
{
    /**
     * System roles available in Marcelo Analytics.
     */
    public const ROLES = [
        'super_admin' => [
            'name' => 'Super Admin',
            'badge_color' => 'bg-purple-100 text-purple-700 border-purple-200',
            'description' => 'Full unrestricted system access, user management, role assignments, and permissions.',
        ],
        'admin' => [
            'name' => 'Admin',
            'badge_color' => 'bg-blue-100 text-blue-700 border-blue-200',
            'description' => 'Practice administration with assigned module permissions.',
        ],
        'manager' => [
            'name' => 'Practice Manager',
            'badge_color' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'description' => 'Operational oversight and management across permitted modules.',
        ],
        'provider' => [
            'name' => 'Provider / Doctor',
            'badge_color' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'description' => 'Clinical and provider-specific portal and patient access.',
        ],
        'developer' => [
            'name' => 'Developer',
            'badge_color' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'description' => 'In-house developer: creates and manages read-only API keys in the Developer Portal.',
        ],
        'staff' => [
            'name' => 'Front Desk / Staff',
            'badge_color' => 'bg-slate-100 text-slate-700 border-slate-200',
            'description' => 'Front desk scheduling, recalls, and daily workflow.',
        ],
    ];

    /** Joins a module key and one of its page keys into a page permission, e.g. "marketing:leads". */
    public const PAGE_SEPARATOR = ':';

    /**
     * Get all module definitions organized by category.
     *
     * A module may declare `pages`: permissions for parts of it, each covering route-name
     * patterns. A user then needs the module and the page. Every route of such a module
     * must be covered by a page or listed in `open_routes` (pages anyone with the module
     * may open); any other route is refused, so a new route stays closed until mapped.
     * `own_shell` marks a module that runs as its own app with its own nav (the Growth
     * Engine), so it cannot lead users to the other modules.
     *
     * @return array<string, array{label: string, modules: array<string, array{name: string, description: string, icon: string, route: string, open_routes?: list<string>, pages?: array<string, array{name: string, routes: list<string>}>}>}>
     */
    public static function getCategories(): array
    {
        return [
            'analytics' => [
                'label' => 'Analytics & Executive',
                'modules' => [
                    'dashboard' => [
                        'name' => 'Dashboard',
                        'description' => 'Practice performance and multi-location overview',
                        'icon' => 'layout-dashboard',
                        'route' => 'dashboard',
                    ],
                    'kpis' => [
                        'name' => 'KPIs',
                        'description' => 'Key performance metrics by doctor, hygiene, and specialty',
                        'icon' => 'bar-chart-2',
                        'route' => 'kpis.index',
                    ],
                    'snapshot' => [
                        'name' => 'Snapshot',
                        'description' => 'High-level practice snapshot and fast stats',
                        'icon' => 'camera',
                        'route' => 'snapshot.index',
                    ],
                ],
            ],
            'operations' => [
                'label' => 'Front Desk & Operations',
                'modules' => [
                    'calendar' => [
                        'name' => 'Calendar',
                        'description' => 'Appointment schedule and chair capacity management',
                        'icon' => 'calendar',
                        'route' => 'calendar.index',
                    ],
                    'front-office' => [
                        'name' => 'Front Office',
                        'description' => 'Task lists, broken appointments, and recall queues',
                        'icon' => 'monitor',
                        'route' => 'front-office.index',
                    ],
                    'hygiene-recall' => [
                        'name' => 'Hygiene Recall',
                        'description' => 'Hygiene recall tracking and patient reactivation',
                        'icon' => 'refresh-cw',
                        'route' => 'hygiene-recall.index',
                    ],
                    'huddle' => [
                        'name' => 'Morning Huddle',
                        'description' => 'Daily team sync and morning meeting dashboard',
                        'icon' => 'users-round',
                        'route' => 'huddle.index',
                    ],
                    'eod' => [
                        'name' => 'EOD Live',
                        'description' => 'End-of-day reconciliation and clinic closing',
                        'icon' => 'zap',
                        'route' => 'eod.index',
                    ],
                    'operations' => [
                        'name' => 'Operations',
                        'description' => 'Multi-location operations and drill-down metrics',
                        'icon' => 'briefcase',
                        'route' => 'operations.index',
                    ],
                ],
            ],
            'clinical' => [
                'label' => 'Clinical & Treatment',
                'modules' => [
                    'patients' => [
                        'name' => 'Patient Portal',
                        'description' => 'Patient charts, treatment plans, and ledgers',
                        'icon' => 'user-square',
                        'route' => 'patients.index',
                    ],
                    'provider-portal' => [
                        'name' => 'Provider Portal',
                        'description' => 'Provider performance, charts, and compensation',
                        'icon' => 'stethoscope',
                        'route' => 'provider-portal.index',
                    ],
                    'tx-miner' => [
                        'name' => 'Tx Miner',
                        'description' => 'Unscheduled treatment plan mining and recovery',
                        'icon' => 'search',
                        'route' => 'tx-miner.index',
                    ],
                ],
            ],
            'financial' => [
                'label' => 'Financials & Billing',
                'modules' => [
                    'financials' => [
                        'name' => 'Financials',
                        'description' => 'Revenue breakdowns, collections, and scorecards',
                        'icon' => 'dollar-sign',
                        'route' => 'financials.index',
                    ],
                    'aging' => [
                        'name' => 'Aging',
                        'description' => 'Accounts receivable aging and live claims balance',
                        'icon' => 'hourglass',
                        'route' => 'aging.index',
                    ],
                    'deposits' => [
                        'name' => 'Deposit Slip',
                        'description' => 'Bank deposit tracking and reconciliation',
                        'icon' => 'file-check-2',
                        'route' => 'deposits.index',
                    ],
                    'rcm' => [
                        'name' => 'RCM',
                        'description' => 'Revenue cycle management and claims processing',
                        'icon' => 'landmark',
                        'route' => 'rcm.index',
                    ],
                ],
            ],
            'growth' => [
                'label' => 'Growth & Marketing',
                'modules' => [
                    'marketing' => [
                        'name' => 'Growth Engine',
                        'description' => 'Per-office website tracking, attribution, Google Ads campaigns, leads and alerts; AI Front Desk; AI Website Builder',
                        'icon' => 'trending-up',
                        'route' => 'marketing.index',
                        'own_shell' => true,
                        'open_routes' => ['marketing.index'],
                        'pages' => [
                            'overview' => ['name' => 'Overview', 'routes' => ['marketing.overview']],
                            'websites' => ['name' => 'Websites', 'routes' => ['marketing.websites']],
                            'journeys' => ['name' => 'Visitor Journeys', 'routes' => ['marketing.journeys']],
                            'funnel' => ['name' => 'Attribution Funnel', 'routes' => ['marketing.funnel']],
                            'channels' => ['name' => 'Channels', 'routes' => ['marketing.channels']],
                            'campaigns' => ['name' => 'Campaigns', 'routes' => ['marketing.campaigns']],
                            'leads' => ['name' => 'Leads', 'routes' => ['marketing.leads']],
                            'alerts' => ['name' => 'Alerts', 'routes' => ['marketing.alerts']],
                            'tracking' => ['name' => 'Tracking Script & sites', 'routes' => ['marketing.tracking', 'marketing.sites.*']],
                            'integrations' => ['name' => 'Integrations (ad accounts)', 'routes' => ['marketing.integrations', 'marketing.google-ads.*', 'marketing.meta-ads.*', 'marketing.ad-accounts.*', 'marketing.ad-campaigns.*']],
                            'front-desk' => ['name' => 'AI Front Desk', 'routes' => ['marketing.front-desk.*']],
                            'site-builder' => ['name' => 'AI Website Builder', 'routes' => ['marketing.site-builder.*']],
                        ],
                    ],
                ],
            ],
            'system' => [
                'label' => 'System & Integrations',
                'modules' => [
                    'offices' => [
                        'name' => 'Offices / Locations',
                        'description' => 'Location configuration and office sync status',
                        'icon' => 'building-2',
                        'route' => 'offices.index',
                    ],
                    'od-explorer' => [
                        'name' => 'OD Data Explorer',
                        'description' => 'Open Dental database queries and table checkpoints',
                        'icon' => 'database',
                        'route' => 'od-explorer.index',
                    ],
                    'sync-manager' => [
                        'name' => 'Data Sync Manager',
                        'description' => 'Real-time synchronization engine and checkpoints',
                        'icon' => 'cloud-lightning',
                        'route' => 'sync-manager.index',
                    ],
                    'provisioner' => [
                        'name' => 'Provisioner',
                        'description' => 'Server provisioning and database configuration',
                        'icon' => 'server-cog',
                        'route' => 'provisioner.index',
                    ],
                    'developer-portal' => [
                        'name' => 'Developer Portal',
                        'description' => 'Create and manage read-only API keys for the bookings API',
                        'icon' => 'code-2',
                        'route' => 'developer.index',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get a flat list of all valid module keys mapped to their info.
     *
     * @return array<string, array{name: string, description: string, icon: string, route: string, category: string}>
     */
    public static function all(): array
    {
        $all = [];
        foreach (self::getCategories() as $catKey => $cat) {
            foreach ($cat['modules'] as $moduleKey => $meta) {
                $all[$moduleKey] = array_merge($meta, ['category' => $cat['label']]);
            }
        }

        return $all;
    }

    /**
     * Get all valid module keys.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Check if a key is an assignable permission: a module or a module page.
     */
    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::all()) || in_array($key, self::pageKeys(), true);
    }

    /**
     * Every assignable permission key: modules, then module pages.
     *
     * @return list<string>
     */
    public static function permissionKeys(): array
    {
        return [...self::keys(), ...self::pageKeys()];
    }

    /**
     * Page definitions of a module ([] when it is not split into pages).
     *
     * @return array<string, array{name: string, routes: list<string>}>
     */
    public static function pages(string $module): array
    {
        return self::all()[$module]['pages'] ?? [];
    }

    public static function pageKey(string $module, string $page): string
    {
        return $module.self::PAGE_SEPARATOR.$page;
    }

    /**
     * Every page permission key, e.g. "marketing:leads".
     *
     * @return list<string>
     */
    public static function pageKeys(): array
    {
        $keys = [];
        foreach (array_keys(self::all()) as $module) {
            foreach (array_keys(self::pages($module)) as $page) {
                $keys[] = self::pageKey($module, $page);
            }
        }

        return $keys;
    }

    /** The module a page permission belongs to, or null for a plain module key. */
    public static function moduleOfPage(string $key): ?string
    {
        return str_contains($key, self::PAGE_SEPARATOR) ? strstr($key, self::PAGE_SEPARATOR, true) : null;
    }

    /** True when the route is open to anyone holding the module (e.g. its hub). */
    public static function isOpenRoute(string $module, string $routeName): bool
    {
        return in_array($routeName, self::all()[$module]['open_routes'] ?? [], true);
    }

    /** The page of a module that covers a route name, or null when none does. */
    public static function pageForRoute(string $module, string $routeName): ?string
    {
        foreach (self::pages($module) as $page => $meta) {
            if (Str::is($meta['routes'], $routeName)) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Get metadata for a specific module key.
     *
     * @return array{name: string, description: string, icon: string, route: string, category: string}|null
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
