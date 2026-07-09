<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    /**
     * Main tabs (slug => label), in display order. Mirrors the Jarvis Operations nav.
     */
    private function tabs(): array
    {
        return [
            'offices' => 'Offices',
            'production-details' => 'Production Details',
            'payors' => 'Payors',
            'performance' => 'Performance',
            'providers' => 'Providers',
            'services' => 'Services',
            'trends' => 'Trends',
            'cancellations' => 'Cancellations',
            'claims' => 'Claims',
            'compliance' => 'Compliance',
            'marketing' => 'Marketing',
            'monthly-practice-scorecards' => 'Monthly Practice Scorecards',
        ];
    }

    /**
     * Subtabs per tab (slug => label). Tabs absent here render no subtab bar.
     * Comparison subtabs (last-year / diff / percent-diff) are handled generically.
     */
    private function subtabsByTab(): array
    {
        return [
            'offices' => [
                'default' => 'Default',
                'last-year' => 'Last Year',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'cancellations' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'performance' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'services' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'providers' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'trends' => [
                'default' => 'Default',
                'compare' => 'Compare',
            ],
            'compliance' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
            'marketing' => [
                'default' => 'Default',
                'patient-analysis' => 'Patient Analysis',
            ],
        ];
    }

    /**
     * Render the portal shell for any tab/subtab URL so that direct loads,
     * reloads and bookmarks all work. The active tab's fragment is fetched by JS.
     */
    public function index(string $tab = 'offices', ?string $subtab = null)
    {
        if (!array_key_exists($tab, $this->tabs())) {
            abort(404);
        }

        return view('operations.index', [
            'tabs' => $this->tabs(),
            'subtabsByTab' => $this->subtabsByTab(),
            'activeTab' => $tab,
            'activeSubtab' => $subtab ?: $this->defaultSubtab($tab),
        ]);
    }

    /**
     * Return the rendered HTML fragment for a single tab (SPA content swap).
     */
    public function data(Request $request, OperationsAnalyticsService $service, string $tab, ?string $subtab = null)
    {
        if (!array_key_exists($tab, $this->tabs())) {
            abort(404);
        }

        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $subtab = $subtab ?: $this->defaultSubtab($tab);
        $clinics = array_filter(explode(',', (string) $request->input('clinics', '')), 'strlen');

        $chrome = [
            'tab' => $tab,
            'subtabs' => $this->subtabsByTab()[$tab] ?? [],
            'activeSubtab' => $subtab,
        ];

        switch ($tab) {
            case 'offices':
                return view('operations.tabs.table', $chrome + [
                    'spec' => $service->offices($start, $end, $subtab, $clinics),
                ]);

            case 'production-details':
                $group = array_values(array_filter(explode(',', (string) $request->input('group', '')), 'strlen'));
                return view('operations.tabs.production-details', $chrome + [
                    'group' => $group,
                    'spec' => $service->productionDetails($start, $end, $group, $clinics),
                ]);

            case 'cancellations':
                return view('operations.tabs.table', $chrome + [
                    'spec' => $service->cancellations($start, $end, $subtab, $clinics),
                ]);

            case 'payors':
                return view('operations.tabs.table', $chrome + [
                    'spec' => $service->payors($start, $end, $clinics),
                ]);

            case 'providers':
                return view('operations.tabs.table', $chrome + [
                    'spec' => $service->providers($start, $end, $subtab, $clinics),
                ]);

            case 'performance':
                return view('operations.tabs.performance', $chrome + [
                    'spec' => $service->performance($start, $end, $subtab, $clinics),
                ]);

            case 'services':
                return view('operations.tabs.services', $chrome + [
                    'spec' => $service->services($start, $end, $subtab, $clinics),
                ]);

            case 'trends':
                $metric = request('metric', 'production');
                $lob = request('lob', '');
                return view('operations.tabs.trends', $chrome + [
                    'spec' => $service->trends($start, $end, $subtab, $clinics, $metric, $lob),
                ]);

            case 'claims':
                return view('operations.tabs.claims', $chrome + [
                    'spec' => $service->claims($start, $end, $subtab, $clinics),
                ]);

            case 'compliance':
                return view('operations.tabs.compliance', $chrome + [
                    'spec' => $service->compliance($start, $end, $subtab, $clinics),
                ]);

            case 'marketing':
                $zip = request('zip', 'ALL');
                return view('operations.tabs.marketing', $chrome + [
                    'spec' => $service->marketing($start, $end, $subtab, $clinics, $zip),
                ]);

            default:
                return view('operations.tabs.placeholder', $chrome + [
                    'label' => $this->tabs()[$tab],
                ]);
        }
    }

    private function defaultSubtab(string $tab): string
    {
        $subtabs = $this->subtabsByTab()[$tab] ?? [];
        return $subtabs ? (string) array_key_first($subtabs) : 'default';
    }
}
