<?php

namespace App\Support;

class ModuleManager
{
    /**
     * System roles available in DDS Manager.
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
        'staff' => [
            'name' => 'Front Desk / Staff',
            'badge_color' => 'bg-slate-100 text-slate-700 border-slate-200',
            'description' => 'Front desk scheduling, recalls, and daily workflow.',
        ],
    ];

    /**
     * Get all module definitions organized by category.
     *
     * @return array<string, array{label: string, modules: array<string, array{name: string, description: string, icon: string, route: string}>}>
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
     * Check if a module key exists.
     */
    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::all());
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
