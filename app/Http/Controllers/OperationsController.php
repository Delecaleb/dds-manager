<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\ProcStatus;
use App\Models\OdPatient;
use App\Models\OdProvider;
use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    public function __construct(
        private readonly ProductionService $production,
        private readonly PatientService $patients,
        private readonly ClinicRegistry $clinics,
    ) {}

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
            'payors' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
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
                'default' => 'Payors - New Patients',
                'payor_existing' => 'Payors - Existing',
                'referral_new_patient' => 'Referrals - New Patients',
                'referral_existing' => 'Referrals - Existing',
                'patient-analysis' => 'Patient Analysis',
            ],
            'monthly-practice-scorecards' => [
                'default' => 'Default',
                'diff-last-year' => 'Diff Last Year',
                'percent-diff-last-year' => 'Percent Diff Last Year',
            ],
        ];
    }

    /**
     * Render the portal shell for any tab/subtab URL so that direct loads,
     * reloads and bookmarks all work. The active tab's fragment is fetched by JS.
     */
    public function index(string $tab = 'offices', ?string $subtab = null)
    {
        if (! array_key_exists($tab, $this->tabs())) {
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
        if (! array_key_exists($tab, $this->tabs())) {
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
                    'spec' => $service->payors($start, $end, $subtab, $clinics),
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

            case 'monthly-practice-scorecards':
                return view('operations.tabs.monthly-practice-scorecards', $chrome + [
                    'spec' => $service->monthlyPracticeScorecards($start, $end, $subtab, $clinics),
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

    /**
     * AJAX endpoint for Operations -> Offices Drill-downs
     */
    public function drilldown(Request $request)
    {
        $metric = $request->input('metric');
        $clinicNum = $request->input('clinic_num');
        $provNum = $request->input('prov_num');
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        if ($request->input('subtab') === 'last-year') {
            $start = Carbon::parse($start)->subYear()->toDateString();
            $end = Carbon::parse($end)->subYear()->toDateString();
        }

        $title = 'Drilldown';
        $columns = [];
        $rows = [];
        $totals = null;

        $providerInfo = null;
        if ($provNum) {
            $p = OdProvider::where('ProvNum', $provNum)->first();
            if ($p) {
                $name = trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? ''));
                $providerInfo = [
                    'name' => $name ?: 'Provider '.$p->ProvNum,
                    'id' => $p->ProvNum.($p->Abbr ? ' - '.$p->Abbr : ''),
                ];
            }
        }

        // Common Provider mapping
        $provMap = OdProvider::all()->mapWithKeys(function ($p) {
            return [$p->ProvNum => $p->LName.($p->PName ? ', '.$p->PName : '')];
        })->toArray();

        // Common Patient mapping
        $mapPatients = function ($patNums) {
            return OdPatient::whereIn('PatNum', $patNums)->get()->mapWithKeys(function ($p) {
                return [$p->PatNum => $p->LName.', '.$p->FName];
            })->toArray();
        };

        if ($metric === 'gross') {
            $title = 'Gross Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ];
            if (! $provNum) {
                $columns[] = ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'];
                $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
            }
            $columns[] = ['key' => 'date', 'label' => 'Dates', 'type' => 'text'];
            $columns[] = ['key' => 'gross', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'];

            $logsQuery = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $totalGross = 0;
            foreach ($logs as $log) {
                $gross = (float) $log->ProcFee;
                if ($gross == 0) {
                    continue;
                }
                $totalGross += $gross;

                $r = [
                    'pat_id' => $log->PatNum,
                    'patient' => [
                        'label' => $patMap[$log->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'date' => date('M d, Y', strtotime($log->ProcDate)),
                    'gross' => $gross,
                ];
                if (! $provNum) {
                    $r['prov_id'] = $log->ProvNum;
                    $r['provider'] = ['label' => $provMap[$log->ProvNum] ?? 'Unknown', 'link' => true];
                }
                $rows[] = $r;
            }
            $totals = ['gross' => $totalGross];

        } elseif ($metric === 'adjustment' || $metric === 'adj_production') {
            $title = $metric === 'adj_production' ? 'Adjustment of Production' : 'Adjustment Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ];
            if (! $provNum) {
                $columns[] = ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'];
                $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
            }
            $columns[] = ['key' => 'date', 'label' => 'Dates', 'type' => 'text'];
            $columns[] = ['key' => 'adj_type', 'label' => 'Adjustment Type', 'type' => 'text'];
            $columns[] = ['key' => 'adj_amt', 'label' => 'Adjustment', 'type' => 'money', 'agg' => 'sum'];

            if ($metric === 'adj_production') {
                $columns[] = ['key' => 'gross', 'label' => 'Gross Production', 'type' => 'money', 'agg' => 'sum'];
                $columns[] = ['key' => 'adj_pct', 'label' => 'Adj %', 'type' => 'percent'];
            }

            $adjsQuery = DB::table('od_adjustments')
                ->select('PatNum', 'ProvNum', 'AdjDate', 'AdjAmt', 'AdjType')
                ->whereBetween('AdjDate', [$start, $end]);

            if ($provNum) {
                $adjsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $adjsQuery->where('ClinicNum', $clinicNum);
            }

            $adjs = $adjsQuery->get();

            $patMap = $mapPatients($adjs->pluck('PatNum')->unique());
            $defMap = DB::table('od_definitions')->where('Category', 1)->pluck('ItemName', 'DefNum')->toArray();

            $totalAdj = 0;
            $totalGrossAll = 0;

            foreach ($adjs as $adj) {
                $amt = (float) $adj->AdjAmt;
                if ($amt == 0) {
                    continue;
                }
                $totalAdj += $amt;

                $row = [
                    'pat_id' => $adj->PatNum,
                    'patient' => [
                        'label' => $patMap[$adj->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'date' => date('M d, Y', strtotime($adj->AdjDate)),
                    'adj_type' => $defMap[$adj->AdjType] ?? 'Type '.$adj->AdjType,
                    'adj_amt' => $amt,
                ];
                if (! $provNum) {
                    $row['prov_id'] = $adj->ProvNum;
                    $row['provider'] = ['label' => $provMap[$adj->ProvNum] ?? 'Unknown', 'link' => true];
                }

                if ($metric === 'adj_production') {
                    $gross = (float) DB::table('od_procedure_logs')
                        ->where('PatNum', $adj->PatNum)
                        ->where('ProcDate', 'like', substr($adj->AdjDate, 0, 10).'%')
                        ->whereIn('ProcStatus', ProcStatus::completed())
                        ->sum('ProcFee');

                    $totalGrossAll += $gross;
                    $row['gross'] = $gross;
                    $row['adj_pct'] = $gross > 0 ? abs($amt) / $gross * 100 : 0;
                }
                $rows[] = $row;
            }

            $totals = ['adj_amt' => $totalAdj];
            if ($metric === 'adj_production') {
                $totals['gross'] = $totalGrossAll;
                $totals['adj_pct'] = $totalGrossAll > 0 ? abs($totalAdj) / $totalGrossAll * 100 : 0;
            }

        } elseif ($metric === 'collection') {
            $title = 'Collection Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ];
            if (! $provNum) {
                $columns[] = ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'];
                $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
                $columns[] = ['key' => 'method', 'label' => 'Payment Method', 'type' => 'text'];
            }
            $columns[] = ['key' => 'date', 'label' => 'Dates', 'type' => 'text'];
            $columns[] = ['key' => 'amount', 'label' => 'Collection', 'type' => 'money', 'agg' => 'sum'];

            $splitsQuery = DB::table('od_pay_splits as s')
                ->leftJoin('od_payments as p', 'p.PayNum', '=', 's.PayNum')
                ->select('s.PatNum', 's.ProvNum', 's.DatePay', 's.SplitAmt', 'p.PayType')
                ->whereBetween('s.DatePay', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $splitsQuery->where('s.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $splitsQuery->where('s.ClinicNum', $clinicNum);
            }

            $splits = $splitsQuery->get();

            $patMap = $mapPatients($splits->pluck('PatNum')->unique());
            $defMap = DB::table('od_definitions')->where('Category', 10)->pluck('ItemName', 'DefNum')->toArray();

            $totalCol = 0;
            foreach ($splits as $sp) {
                $amt = (float) $sp->SplitAmt;
                if ($amt == 0) {
                    continue;
                }
                $totalCol += $amt;

                $r = [
                    'date' => date('M d, Y', strtotime($sp->DatePay)),
                    'pat_id' => $sp->PatNum,
                    'patient' => [
                        'label' => $patMap[$sp->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'amount' => $amt,
                ];
                if (! $provNum) {
                    $r['prov_id'] = $sp->ProvNum;
                    $r['provider'] = ['label' => $provMap[$sp->ProvNum] ?? 'Unknown', 'link' => true];
                    $r['method'] = $defMap[$sp->PayType] ?? 'Type '.$sp->PayType;
                }
                $rows[] = $r;
            }
            $totals = ['amount' => $totalCol];

        } elseif ($metric === 'net' || $metric === 'coll_pct') {
            $title = $metric === 'coll_pct' ? 'Collection % Breakdown' : 'Net Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ];
            if (! $provNum) {
                $columns[] = ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'];
                $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
            }
            $columns[] = ['key' => 'date', 'label' => 'Dates', 'type' => 'text'];
            $columns[] = ['key' => 'net', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'];
            if ($metric === 'coll_pct') {
                $columns[] = ['key' => 'coll', 'label' => 'Collection', 'type' => 'money', 'agg' => 'sum'];
                $columns[] = ['key' => 'coll_pct', 'label' => 'Collection %', 'type' => 'percent'];
            }

            $logsQuery = DB::table('od_procedure_logs')->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')->whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$start, $end]);
            $adjsQuery = DB::table('od_adjustments')->select('PatNum', 'ProvNum', 'AdjDate', 'AdjAmt')->whereBetween('AdjDate', [$start, $end]);
            $wosQuery = DB::table('od_claim_procs')->select('PatNum', 'ProvNum', 'ProcDate', 'WriteOff')->whereBetween('ProcDate', [$start, $end]);
            $splitsQuery = DB::table('od_pay_splits')->select('PatNum', 'ProvNum', 'DatePay', 'SplitAmt')->whereBetween('DatePay', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
                $splitsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
                $splitsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();
            $adjs = $adjsQuery->get();
            $wos = $wosQuery->get();
            $splits = $metric === 'coll_pct' ? $splitsQuery->get() : [];

            $map = [];
            $allPats = [];

            $buildKey = function ($pat, $prov, $d) use (&$allPats) {
                $allPats[] = $pat;

                return $pat.'|'.$prov.'|'.substr($d, 0, 10);
            };

            foreach ($logs as $l) {
                $k = $buildKey($l->PatNum, $l->ProvNum, $l->ProcDate);
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $l->PatNum, 'prov' => $l->ProvNum, 'date' => substr($l->ProcDate, 0, 10), 'gross' => 0, 'adj' => 0, 'wo' => 0, 'coll' => 0];
                }
                $map[$k]['gross'] += (float) $l->ProcFee;
            }
            foreach ($adjs as $a) {
                $k = $buildKey($a->PatNum, $a->ProvNum, $a->AdjDate);
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $a->PatNum, 'prov' => $a->ProvNum, 'date' => substr($a->AdjDate, 0, 10), 'gross' => 0, 'adj' => 0, 'wo' => 0, 'coll' => 0];
                }
                $map[$k]['adj'] += (float) $a->AdjAmt;
            }
            foreach ($wos as $w) {
                $k = $buildKey($w->PatNum, $w->ProvNum, $w->ProcDate);
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $w->PatNum, 'prov' => $w->ProvNum, 'date' => substr($w->ProcDate, 0, 10), 'gross' => 0, 'adj' => 0, 'wo' => 0, 'coll' => 0];
                }
                $map[$k]['wo'] += (float) $w->WriteOff;
            }
            foreach ($splits as $s) {
                $k = $buildKey($s->PatNum, $s->ProvNum, $s->DatePay);
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $s->PatNum, 'prov' => $s->ProvNum, 'date' => substr($s->DatePay, 0, 10), 'gross' => 0, 'adj' => 0, 'wo' => 0, 'coll' => 0];
                }
                $map[$k]['coll'] += (float) $s->SplitAmt;
            }

            $patMap = $mapPatients(array_unique($allPats));
            $totNet = 0;
            $totColl = 0;

            foreach ($map as $m) {
                $net = $this->production->netFrom((float) $m['gross'], (float) $m['adj'], (float) $m['wo']);
                $coll = $m['coll'];

                if (round($net, 2) == 0 && round($coll, 2) == 0 && $metric === 'coll_pct') {
                    continue;
                }
                if (round($net, 2) == 0 && $metric === 'net') {
                    continue;
                }

                $totNet += $net;
                $totColl += $coll;

                $row = [
                    'pat_id' => $m['pat'],
                    'patient' => ['label' => $patMap[$m['pat']] ?? 'Unknown', 'link' => true],
                    'date' => date('M d, Y', strtotime($m['date'])),
                    'net' => $net,
                ];
                if (! $provNum) {
                    $row['prov_id'] = $m['prov'];
                    $row['provider'] = ['label' => $provMap[$m['prov']] ?? 'Unknown', 'link' => true];
                }
                if ($metric === 'coll_pct') {
                    $row['coll'] = $coll;
                    $row['coll_pct'] = $net > 0 ? ($coll / $net) * 100 : 0;
                }
                $rows[] = $row;
            }

            usort($rows, function ($a, $b) {
                return strtotime($b['date']) <=> strtotime($a['date']);
            });

            $totals = ['net' => $totNet];
            if ($metric === 'coll_pct') {
                $totals['coll'] = $totColl;
                $totals['coll_pct'] = $totNet > 0 ? ($totColl / $totNet) * 100 : 0;
            }

        } elseif ($metric === 'pts_visit') {
            $title = 'Pts Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'visit_days', 'label' => 'Visit Days', 'type' => 'text'],
                ['key' => 'count', 'label' => '# of Visit', 'type' => 'number', 'agg' => 'sum'],
            ];

            $logsQuery = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProcDate')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());
            $patVisits = [];

            foreach ($logs as $log) {
                $d = substr($log->ProcDate, 0, 10);
                $patVisits[$log->PatNum][$d] = true;
            }

            $totalVisits = 0;
            foreach ($patVisits as $patNum => $days) {
                $count = count($days);
                $totalVisits += $count;
                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'visit_days' => implode(', ', array_map(function ($d) {
                        return date('M d, Y', strtotime($d));
                    }, array_keys($days))),
                    'count' => $count,
                ];
            }
            $totals = ['count' => $totalVisits];

        } elseif ($metric === 'npt_visit') {
            $title = 'Npt Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'visit_days', 'label' => 'Visit Days', 'type' => 'text'],
                ['key' => 'count', 'label' => '# of Visit', 'type' => 'number', 'agg' => 'sum'],
            ];

            $firstVisitSubQ = $this->patients->firstVisitCohort();

            $logsQuery = DB::table('od_procedure_logs as pl')
                ->joinSub($firstVisitSubQ, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
                ->select('pl.PatNum', 'pl.ProcDate')
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereRaw('LEFT(pl.ProcDate, 10) = LEFT(fv.first_date, 10)')
                ->whereBetween('pl.ProcDate', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $logsQuery->where('pl.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('pl.ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());
            $patVisits = [];

            foreach ($logs as $log) {
                $d = date('Y-m-d', strtotime($log->ProcDate));
                $patVisits[$log->PatNum][$d] = true;
            }

            $totalVisits = 0;
            foreach ($patVisits as $patNum => $days) {
                $count = count($days);
                $totalVisits += $count;
                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'visit_days' => implode(', ', array_map(function ($d) {
                        return date('M d, Y', strtotime($d));
                    }, array_keys($days))),
                    'count' => $count,
                ];
            }
            $totals = ['count' => $totalVisits];

        } elseif ($metric === 'cancellation') {
            $title = 'Cancellation Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'appt_id', 'label' => 'Appt ID', 'type' => 'text'],
                ['key' => 'appt_date', 'label' => 'Appt Date', 'type' => 'text'],
                ['key' => 'note', 'label' => 'Note', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'agg' => 'sum'],
            ];

            $query = DB::table('od_appointments as a')
                ->select(
                    'a.AptNum',
                    'a.PatNum',
                    'a.ProvNum',
                    'a.AptDateTime',
                    'a.Note',
                    'a.ProcDescript'
                )
                ->where('a.AptStatus', '5')
                ->whereNotIn('a.AptNum', [85716, 85845, 85891, 85892, 85468, 85466, 85947])
                ->whereBetween('a.AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $query->where('a.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('a.ClinicNum', $clinicNum);
            }

            $appts = $query->get();

            $patMap = $mapPatients($appts->pluck('PatNum')->unique());

            $providers = OdProvider::whereIn('ProvNum', $appts->pluck('ProvNum')->unique())
                ->get()
                ->keyBy('ProvNum');

            $aptNums = $appts->pluck('AptNum')->unique();
            $fees = [];
            if ($aptNums->isNotEmpty()) {
                $fees = DB::table('od_procedure_logs')
                    ->selectRaw('AptNum, SUM(ProcFee) as total_fee')
                    ->whereIn('AptNum', $aptNums)
                    ->groupBy('AptNum')
                    ->pluck('total_fee', 'AptNum')
                    ->all();
            }

            $totAmt = 0;
            foreach ($appts as $apt) {
                $p = $providers[$apt->ProvNum] ?? null;
                $provName = $p ? trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? '')) : ($provMap[$apt->ProvNum] ?? 'Unknown');
                $provAbbr = $p ? ($p->Abbr ?? '') : '';
                $provIdStr = $apt->ProvNum.($provAbbr ? ' - '.strtoupper($provAbbr) : '');

                $amt = (float) ($fees[$apt->AptNum] ?? 0);
                $totAmt += $amt;

                $noteText = trim(($apt->Note ?: $apt->ProcDescript) ?: 'No note');

                $rows[] = [
                    'pat_id' => $apt->PatNum,
                    'patient' => [
                        'label' => $patMap[$apt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provIdStr,
                    'provider' => [
                        'label' => $provName,
                        'link' => true,
                        'prov_num' => $apt->ProvNum,
                    ],
                    'prov_num' => $apt->ProvNum,
                    'appt_id' => $apt->AptNum,
                    'appt_date' => date('M d, Y', strtotime($apt->AptDateTime)),
                    'note' => $noteText,
                    'type' => 'Cancellation',
                    'amount' => $amt,
                ];
            }

            $totals = ['amount' => $totAmt];

        } elseif ($metric === 'working_days') {
            $title = 'Working Days Breakdown';
            $columns = [
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'pts_visits', 'label' => 'Patient Visits', 'type' => 'number', 'agg' => 'sum'],
                ['key' => 'procedures', 'label' => 'Procedures', 'type' => 'number', 'agg' => 'sum'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $query = DB::table('od_procedure_logs')
                ->selectRaw('ProcDate, COUNT(DISTINCT PatNum) as pts_visits, COUNT(*) as procedures, SUM(ProcFee) as production')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $query->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('ClinicNum', $clinicNum);
            }

            $logs = $query->groupBy('ProcDate')->get();

            $totVisits = 0;
            $totProcs = 0;
            $totProd = 0;
            foreach ($logs as $l) {
                $v = (int) $l->pts_visits;
                $p = (int) $l->procedures;
                $pr = (float) $l->production;
                $totVisits += $v;
                $totProcs += $p;
                $totProd += $pr;

                $rows[] = [
                    'date' => date('M d, Y', strtotime($l->ProcDate)),
                    'pts_visits' => $v,
                    'procedures' => $p,
                    'production' => $pr,
                ];
            }
            $totals = [
                'pts_visits' => $totVisits,
                'procedures' => $totProcs,
                'production' => $totProd,
            ];
        } elseif ($metric === 'unique_pts') {
            $title = 'Unique Patients Breakdown';
            $columns = [
                ['key' => 'office', 'label' => 'Office', 'type' => 'text'],
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'providers', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'visit_days', 'label' => 'Visit Dates', 'type' => 'text'],
                ['key' => 'count', 'label' => 'Visit Count', 'type' => 'number', 'agg' => 'sum'],
                ['key' => 'services', 'label' => 'Services', 'type' => 'number', 'agg' => 'sum'],
            ];

            $logs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProcDate', 'ProvNum')
                ->where('ClinicNum', $clinicNum)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $clinicName = $this->clinics->name((int) $clinicNum);
            $patData = [];

            foreach ($logs as $log) {
                $d = substr($log->ProcDate, 0, 10);
                if (! isset($patData[$log->PatNum])) {
                    $patData[$log->PatNum] = [
                        'days' => [],
                        'provs' => [],
                        'services' => 0,
                    ];
                }
                $patData[$log->PatNum]['days'][$d] = true;
                if (! empty($provMap[$log->ProvNum])) {
                    $patData[$log->PatNum]['provs'][$provMap[$log->ProvNum]] = true;
                }
                $patData[$log->PatNum]['services']++;
            }

            $totalVisits = 0;
            $totalServices = 0;
            foreach ($patData as $patNum => $data) {
                $count = count($data['days']);
                $totalVisits += $count;
                $totalServices += $data['services'];

                $rows[] = [
                    'office' => $clinicName,
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'providers' => implode(', ', array_keys($data['provs'])),
                    'visit_days' => implode(', ', array_map(function ($d) {
                        return date('M d, Y', strtotime($d));
                    }, array_keys($data['days']))),
                    'count' => $count,
                    'services' => $data['services'],
                ];
            }
            $totals = [
                'count' => $totalVisits,
                'services' => $totalServices,
            ];
        } elseif ($metric === 'npt_visit') {
            $title = 'Npt Visit Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'first_visit', 'label' => 'First Visit Day', 'type' => 'text'],
                ['key' => 'service_codes', 'label' => 'Service Codes', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $firstVisitSubQ = $this->patients->firstVisitCohort();

            $nptLogs = DB::table('od_procedure_logs as pl')
                ->joinSub($firstVisitSubQ, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
                ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->select('pl.PatNum', 'pl.ProcDate', 'pl.ProcFee', 'pc.ProcCode', 'fv.first_date')
                ->where('pl.ClinicNum', $clinicNum)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$start, $end])
                ->whereBetween('fv.first_date', [$start, $end])
                ->get();

            $patMap = $mapPatients($nptLogs->pluck('PatNum')->unique());

            $patData = [];
            foreach ($nptLogs as $log) {
                if (! isset($patData[$log->PatNum])) {
                    $patData[$log->PatNum] = [
                        'first_date' => $log->first_date,
                        'codes' => [],
                        'production' => 0,
                    ];
                }
                $patData[$log->PatNum]['codes'][$log->ProcCode] = true;
                $patData[$log->PatNum]['production'] += (float) $log->ProcFee;
            }

            $totalProduction = 0;
            foreach ($patData as $patNum => $data) {
                $totalProduction += $data['production'];
                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'first_visit' => date('M d, Y', strtotime($data['first_date'])),
                    'service_codes' => implode(', ', array_keys($data['codes'])),
                    'production' => $data['production'],
                ];
            }

            $totals = ['production' => $totalProduction];
        } elseif ($metric === 'new_patient_dollars') {
            $title = 'New Patient $ Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'first_visit', 'label' => 'First Visit Day', 'type' => 'text'],
                ['key' => 'providers', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'insurance_carrier', 'label' => 'Insurance Carrier Name', 'type' => 'text'],
                ['key' => 'referral_source', 'label' => 'Referral Source', 'type' => 'text'],
                ['key' => 'service_codes', 'label' => 'Procedure Codes', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $firstVisitSubQ = $this->patients->firstVisitCohort();

            $nptLogs = DB::table('od_procedure_logs as pl')
                ->joinSub($firstVisitSubQ, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
                ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->select('pl.PatNum', 'pl.ProvNum', 'pl.ProcDate', 'pl.ProcFee', 'pc.ProcCode', 'fv.first_date')
                ->where('pl.ClinicNum', $clinicNum)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$start, $end])
                ->whereBetween('fv.first_date', [$start, $end])
                ->get();

            $patMap = $mapPatients($nptLogs->pluck('PatNum')->unique());

            $patData = [];
            foreach ($nptLogs as $log) {
                if (! isset($patData[$log->PatNum])) {
                    $patData[$log->PatNum] = [
                        'first_date' => $log->first_date,
                        'provs' => [],
                        'codes' => [],
                        'production' => 0,
                    ];
                }
                if (! empty($provMap[$log->ProvNum])) {
                    $patData[$log->PatNum]['provs'][$provMap[$log->ProvNum]] = true;
                }
                $patData[$log->PatNum]['codes'][$log->ProcCode] = true;
                $patData[$log->PatNum]['production'] += (float) $log->ProcFee;
            }

            $totalProduction = 0;
            foreach ($patData as $patNum => $data) {
                $totalProduction += $data['production'];
                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'first_visit' => date('M d, Y', strtotime($data['first_date'])),
                    'providers' => implode(', ', array_keys($data['provs'])),
                    'insurance_carrier' => 'N/A',
                    'referral_source' => 'N/A',
                    'service_codes' => implode(', ', array_keys($data['codes'])),
                    'production' => $data['production'],
                ];
            }
            $totals = ['production' => $totalProduction];

        } elseif ($metric === 'act_pts') {
            $title = 'Act Pts w/ Reservation Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'first_visit', 'label' => 'First Visit Day', 'type' => 'text'],
                ['key' => 'visited', 'label' => 'Visited', 'type' => 'text'],
            ];

            $startWindow = date('Y-m-d', strtotime('-24 months', strtotime($end))).' 00:00:00';

            $firstVisitSubQ = $this->patients->firstVisitCohort();

            $activePts = DB::table('od_procedure_logs as pl')
                ->joinSub($firstVisitSubQ, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
                ->select('pl.PatNum', 'fv.first_date')
                ->where('pl.ClinicNum', $clinicNum)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$startWindow, $end.' 23:59:59'])
                ->groupBy('pl.PatNum', 'fv.first_date')
                ->get();

            $patNums = $activePts->pluck('PatNum')->unique();
            $patMap = $mapPatients($patNums);

            $reservations = [];
            if ($patNums->isNotEmpty()) {
                $reservations = DB::table('od_appointments')
                    ->select('PatNum')
                    ->whereIn('PatNum', $patNums)
                    ->whereIn('AptStatus', [1, 4])
                    ->where('AptDateTime', '>=', now()->toDateString())
                    ->groupBy('PatNum')
                    ->pluck('PatNum')->mapWithKeys(function ($item) {
                        return [$item => true];
                    })->all();
            }

            foreach ($activePts as $pt) {
                $rows[] = [
                    'pat_id' => $pt->PatNum,
                    'patient' => [
                        'label' => $patMap[$pt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'first_visit' => date('M d, Y', strtotime($pt->first_date)),
                    'visited' => isset($reservations[$pt->PatNum]) ? 'Yes' : 'No',
                ];
            }
            $totals = [];

        } elseif ($metric === 'act_pts_count') {
            $title = 'Act Pts Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'provider_ids', 'label' => 'Provider IDs', 'type' => 'text'],
                ['key' => 'providers', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'visits', 'label' => 'Visits', 'type' => 'number', 'agg' => 'sum'],
            ];

            $startWindow = date('Y-m-d', strtotime('-24 months', strtotime($end))).' 00:00:00';

            $logs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate')
                ->where('ClinicNum', $clinicNum)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$startWindow, $end.' 23:59:59'])
                ->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $patData = [];
            foreach ($logs as $log) {
                $d = substr($log->ProcDate, 0, 10);
                if (! isset($patData[$log->PatNum])) {
                    $patData[$log->PatNum] = [
                        'days' => [],
                        'provs' => [],
                    ];
                }
                $patData[$log->PatNum]['days'][$d] = true;
                if (! empty($provMap[$log->ProvNum])) {
                    $patData[$log->PatNum]['provs'][$log->ProvNum] = true;
                }
            }

            $totalVisits = 0;
            foreach ($patData as $patNum => $data) {
                $count = count($data['days']);
                $totalVisits += $count;

                $provIds = array_keys($data['provs']);
                $provNames = [];
                foreach ($provIds as $id) {
                    $provNames[] = $provMap[$id] ?? '';
                }

                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'provider_ids' => implode(', ', $provIds),
                    'providers' => implode(', ', array_filter($provNames)),
                    'visits' => $count,
                ];
            }
            $totals = ['visits' => $totalVisits];

        } elseif ($metric === 'retention') {
            $title = 'Retention Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'visited', 'label' => 'Visited', 'type' => 'text'],
            ];

            $prior18m = date('Y-m-d', strtotime('-18 months', strtotime($start)));
            $priorEnd = date('Y-m-d', strtotime('-1 day', strtotime($start)));

            $activePts = DB::table('od_procedure_logs as pl')
                ->leftJoin('od_procedures as pc', 'pc.CodeNum', '=', 'pl.CodeNum')
                ->selectRaw("
                    pl.PatNum,
                    MAX(CASE WHEN pc.ProcCode IN ('D0120','D0140','D0150') THEN 1 ELSE 0 END) AS had_exam
                ")
                ->where('pl.ClinicNum', $clinicNum)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$prior18m.' 00:00:00', $priorEnd.' 23:59:59'])
                ->groupBy('pl.PatNum')
                ->get();

            $patMap = $mapPatients($activePts->pluck('PatNum')->unique());

            foreach ($activePts as $pt) {
                $rows[] = [
                    'pat_id' => $pt->PatNum,
                    'patient' => [
                        'label' => $patMap[$pt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'visited' => $pt->had_exam ? 'Yes' : 'No',
                ];
            }
            $totals = [];
        }

        return view('components.app-components.drilldown.table-content', compact('title', 'columns', 'rows', 'totals', 'providerInfo'));
    }
}
