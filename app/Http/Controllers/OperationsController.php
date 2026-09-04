<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\ProcStatus;
use App\Models\OdPatient;
use App\Models\OdProvider;
use App\Models\Office;
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
        private readonly PatientVisitService $patientVisits,
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
        $end = $request->input('end_date', now()->endOfMonth()->toDateString());
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
                $metric = request('metric', 'BYO Production');
                $lob = request('lob', '');

                return view('operations.tabs.trends', $chrome + [
                    'metric' => $metric,
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
        $start = $request->input('start_date', $request->input('start', now()->startOfMonth()->toDateString()));
        $end = $request->input('end_date', $request->input('end', now()->toDateString()));

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

        $providers = OdProvider::all()->keyBy('ProvNum');

        $resolveProvName = function ($provNum) use ($providers) {
            $p = $providers->get($provNum);
            if (! $p) {
                return 'Provider '.$provNum;
            }
            $abbr = trim($p->Abbr ?? '');
            $lName = trim($p->LName ?? '');
            $fName = trim($p->FName ?: $p->PName ?: ($p->PreferredName ?? ''));
            if ($fName !== '' && $lName !== '') {
                return "$fName $lName";
            }

            return $lName ?: ($fName ?: ($abbr ?: 'Provider '.$provNum));
        };

        // Common Provider mapping
        $provMap = $providers->mapWithKeys(function ($p) use ($resolveProvName) {
            return [$p->ProvNum => $resolveProvName($p->ProvNum)];
        })->toArray();

        // Common Patient mapping
        $mapPatients = function ($patNums) {
            return OdPatient::whereIn('PatNum', $patNums)->get()->mapWithKeys(function ($p) {
                return [$p->PatNum => $p->LName.', '.$p->FName];
            })->toArray();
        };

        $formatProv = function ($pNum) use ($providers, $provMap) {
            if (! $pNum) {
                return ['id' => '—', 'name' => ['label' => 'Unknown', 'link' => false, 'prov_num' => 0]];
            }
            $p = $providers->get($pNum);
            $abbr = $p ? ($p->Abbr ?? '') : '';
            $provIdStr = $pNum.($abbr ? ' - '.strtoupper($abbr) : '');
            $provName = $p ? trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? '')) : ($provMap[$pNum] ?? 'Unknown');
            if (! $provName) {
                $provName = $provMap[$pNum] ?? ('Provider '.$pNum);
            }

            return [
                'id' => $provIdStr,
                'name' => [
                    'label' => $provName,
                    'link' => true,
                    'prov_num' => $pNum,
                ],
            ];
        };

        $officeId = $request->filled('office_id') ? (int) $request->input('office_id') : (Office::getActiveOfficeId() ?? 1);

        if ($metric === 'sched_production') {
            $title = 'Scheduled Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'appt_id', 'label' => 'Appt ID', 'type' => 'text'],
                ['key' => 'appt_date', 'label' => 'Appt Date / Time', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $statusLabels = [
                '1' => 'Scheduled',
                '2' => 'Complete',
                '3' => 'UnschedList',
                '4' => 'ASAP',
                '5' => 'Broken',
                '6' => 'Planned',
                '7' => 'PtNote',
                '8' => 'PtNoteCompleted',
            ];

            $snapQuery = DB::table('od_appointment_schedule_snapshots')
                ->where('office_id', $officeId)
                ->whereBetween('snapshot_date', [$start, $end]);

            if ($provNum) {
                $snapQuery->where('prov_num', $provNum);
            } elseif ($clinicNum) {
                $snapQuery->where('clinic_num', $clinicNum);
            }

            if ($snapQuery->exists()) {
                $snapAppts = $snapQuery->orderBy('apt_date_time')->get();
                $patMap = $mapPatients($snapAppts->pluck('pat_num')->unique());
                $totProd = 0;

                foreach ($snapAppts as $apt) {
                    $fee = (float) $apt->sched_production;
                    if ($fee <= 0) {
                        continue;
                    }
                    $totProd += $fee;
                    $provInfo = $formatProv($apt->prov_num);
                    $st = (string) $apt->apt_status;

                    $rows[] = [
                        'pat_id' => $apt->pat_num,
                        'patient' => [
                            'label' => $patMap[$apt->pat_num] ?? 'Unknown',
                            'link' => true,
                        ],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'prov_num' => $apt->prov_num,
                        'appt_id' => $apt->apt_num,
                        'appt_date' => $apt->apt_date_time ? date('M d, Y h:i A', strtotime($apt->apt_date_time)) : '—',
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'production' => $fee,
                    ];
                }
                $totals = ['production' => $totProd];
            } else {
                $apptsQuery = DB::table('od_appointments as a')
                    ->join('od_procedure_logs as pl', 'a.AptNum', '=', 'pl.AptNum')
                    ->select(
                        'a.AptNum',
                        'a.PatNum',
                        'a.ProvNum',
                        'a.AptDateTime',
                        'a.AptStatus',
                        DB::raw('SUM(pl.ProcFee) as total_fee')
                    )
                    ->whereNotIn('a.AptStatus', [6])
                    ->whereBetween('a.AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->groupBy('a.AptNum', 'a.PatNum', 'a.ProvNum', 'a.AptDateTime', 'a.AptStatus');

                if ($provNum) {
                    $apptsQuery->where('a.ProvNum', $provNum);
                } elseif ($clinicNum) {
                    $apptsQuery->where('a.ClinicNum', $clinicNum);
                }

                $appts = $apptsQuery->orderBy('a.AptDateTime')->get();
                $patMap = $mapPatients($appts->pluck('PatNum')->unique());

                $totProd = 0;
                foreach ($appts as $apt) {
                    $fee = (float) $apt->total_fee;
                    if ($fee <= 0) {
                        continue;
                    }
                    $totProd += $fee;
                    $provInfo = $formatProv($apt->ProvNum);
                    $st = (string) $apt->AptStatus;

                    $rows[] = [
                        'pat_id' => $apt->PatNum,
                        'patient' => [
                            'label' => $patMap[$apt->PatNum] ?? 'Unknown',
                            'link' => true,
                        ],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'prov_num' => $apt->ProvNum,
                        'appt_id' => $apt->AptNum,
                        'appt_date' => date('M d, Y h:i A', strtotime($apt->AptDateTime)),
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'production' => $fee,
                    ];
                }
                $totals = ['production' => $totProd];
            }

        } elseif ($metric === 'actual_production') {
            $title = 'Actual Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'code', 'label' => 'Procedure Code', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Actual Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $query = DB::table('od_procedure_logs as pl')
                ->leftJoin('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->select(
                    'pl.ProcNum',
                    'pl.PatNum',
                    'pl.ProvNum',
                    'pl.ProcDate',
                    'pl.ProcFee',
                    'pc.ProcCode',
                    'pc.Descript'
                )
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$start, $end]);

            if ($provNum) {
                $query->where('pl.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('pl.ClinicNum', $clinicNum);
            }

            $items = $query->orderBy('pl.ProcDate')->get();
            $patMap = $mapPatients($items->pluck('PatNum')->unique());

            $totProd = 0;
            foreach ($items as $item) {
                $fee = (float) $item->ProcFee;
                $totProd += $fee;
                $provInfo = $formatProv($item->ProvNum);

                $rows[] = [
                    'pat_id' => $item->PatNum,
                    'patient' => [
                        'label' => $patMap[$item->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'date' => date('M d, Y', strtotime($item->ProcDate)),
                    'code' => $item->ProcCode ?: 'Unknown',
                    'description' => $item->Descript ?: 'Procedure',
                    'production' => $fee,
                ];
            }
            $totals = ['production' => $totProd];

        } elseif ($metric === 'actual_collection') {
            $title = 'Actual Collection Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Payment Type', 'type' => 'text'],
                ['key' => 'collection', 'label' => 'Collection', 'type' => 'money', 'agg' => 'sum'],
            ];

            $splitsQuery = DB::table('od_pay_splits')
                ->select('PatNum', 'ProvNum', 'DatePay', 'SplitAmt')
                ->whereBetween('DatePay', [$start.' 00:00:00', $end.' 23:59:59']);
            $claimsQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'ProvNum', 'DateCP as DatePay', 'InsPayAmt as SplitAmt')
                ->whereBetween('DateCP', [$start.' 00:00:00', $end.' 23:59:59'])
                ->where('Status', '!=', 0);

            if ($provNum) {
                $splitsQuery->where('ProvNum', $provNum);
                $claimsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $splitsQuery->where('ClinicNum', $clinicNum);
                $claimsQuery->where('ClinicNum', $clinicNum);
            }

            $splits = $splitsQuery->get();
            $claims = $claimsQuery->get();

            $allPats = $splits->pluck('PatNum')->merge($claims->pluck('PatNum'))->unique();
            $patMap = $mapPatients($allPats);

            $totCol = 0;
            foreach ($splits as $sp) {
                $amt = (float) $sp->SplitAmt;
                if ($amt == 0) {
                    continue;
                }
                $totCol += $amt;
                $provInfo = $formatProv($sp->ProvNum);
                $rows[] = [
                    'pat_id' => $sp->PatNum,
                    'patient' => ['label' => $patMap[$sp->PatNum] ?? 'Unknown', 'link' => true],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'prov_num' => $sp->ProvNum,
                    'date' => date('M d, Y', strtotime($sp->DatePay)),
                    'type' => 'Patient Payment',
                    'collection' => $amt,
                    'amount' => $amt,
                ];
            }

            foreach ($claims as $cp) {
                $amt = (float) $cp->SplitAmt;
                if ($amt == 0) {
                    continue;
                }
                $totCol += $amt;
                $provInfo = $formatProv($cp->ProvNum);
                $rows[] = [
                    'pat_id' => $cp->PatNum,
                    'patient' => ['label' => $patMap[$cp->PatNum] ?? 'Insurance Payment', 'link' => true],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'prov_num' => $cp->ProvNum,
                    'date' => date('M d, Y', strtotime($cp->DatePay)),
                    'type' => 'Insurance Payment',
                    'collection' => $amt,
                    'amount' => $amt,
                ];
            }
            $totals = ['collection' => $totCol, 'amount' => $totCol];

        } elseif ($metric === 'actual_pts_visit') {
            $title = 'Actual Pts Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'gross', 'label' => 'Gross production', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'adjustment', 'label' => 'Adjustment', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'writeoff', 'label' => 'Writeoff', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'visited', 'label' => 'Visited', 'type' => 'number', 'agg' => 'sum'],
                ['key' => 'production', 'label' => 'Production ($)', 'type' => 'money', 'agg' => 'sum'],
            ];

            $logsQuery = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            $adjsQuery = DB::table('od_adjustments')
                ->select('PatNum', 'ProvNum', 'AdjDate', 'AdjAmt')
                ->whereBetween('AdjDate', [$start, $end]);

            $wosQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'WriteOff')
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();
            $adjs = $adjsQuery->get();
            $wos = $wosQuery->get();

            $allPats = $logs->pluck('PatNum')->merge($adjs->pluck('PatNum'))->merge($wos->pluck('PatNum'))->unique();
            $patMap = $mapPatients($allPats);

            $pats = [];
            foreach ($logs as $l) {
                $pNum = $l->PatNum;
                $d = substr((string) $l->ProcDate, 0, 10);
                if (! isset($pats[$pNum])) {
                    $pats[$pNum] = ['prov' => $l->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0, 'dates' => []];
                }
                $pats[$pNum]['gross'] += (float) $l->ProcFee;
                $pats[$pNum]['dates'][$d] = true;
            }

            foreach ($adjs as $a) {
                $pNum = $a->PatNum;
                if (! isset($pats[$pNum])) {
                    $pats[$pNum] = ['prov' => $a->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0, 'dates' => []];
                }
                $pats[$pNum]['adj'] += (float) $a->AdjAmt;
            }

            foreach ($wos as $w) {
                $pNum = $w->PatNum;
                if (! isset($pats[$pNum])) {
                    $pats[$pNum] = ['prov' => $w->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0, 'dates' => []];
                }
                $pats[$pNum]['wo'] += (float) $w->WriteOff;
            }

            $totGross = 0;
            $totAdj = 0;
            $totWo = 0;
            $totVisited = 0;
            $totProd = 0;

            foreach ($pats as $pNum => $info) {
                $gross = (float) $info['gross'];
                $adj = (float) $info['adj'];
                $wo = (float) $info['wo'];
                $visited = count($info['dates']);
                $net = $gross + $adj - $wo;

                $totGross += $gross;
                $totAdj += $adj;
                $totWo += $wo;
                $totVisited += $visited;
                $totProd += $net;

                $provInfo = $formatProv($info['prov']);

                $rows[] = [
                    'pat_id' => $pNum,
                    'patient' => ['label' => $patMap[$pNum] ?? 'Unknown', 'link' => true],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'gross' => $gross,
                    'adjustment' => $adj,
                    'writeoff' => $wo,
                    'visited' => $visited,
                    'production' => $net,
                ];
            }

            $totals = [
                'gross' => $totGross,
                'adjustment' => $totAdj,
                'writeoff' => $totWo,
                'visited' => $totVisited,
                'production' => $totProd,
            ];

        } elseif ($metric === 'actual_npt_visit') {
            $title = 'Actual New Patient Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'visit_date', 'label' => 'First Visit Date', 'type' => 'text'],
            ];

            $clinicNums = (! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0) ? [$clinicNum] : [];
            $nptVisits = $this->patientVisits->newPatientVisits($start, $end, $clinicNums);

            foreach ($nptVisits as $v) {
                $pNum = $v['prov_num'] ?? 0;
                $provInfo = $formatProv($pNum);
                $rows[] = [
                    'pat_id' => $v['patient_id'],
                    'patient' => ['label' => $v['patient_name'] ?: 'Unknown', 'link' => true],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'visit_date' => date('M d, Y', strtotime($v['dates'] ?? $start)),
                ];
            }
            $totals = [];

        } elseif ($metric === 'sched_pts_visit') {
            $title = 'Scheduled Patient Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'appt_id', 'label' => 'Appt ID', 'type' => 'text'],
                ['key' => 'appt_date', 'label' => 'Appt Date / Time', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
            ];

            $statusLabels = [
                '1' => 'Scheduled',
                '2' => 'Complete',
                '3' => 'UnschedList',
                '4' => 'ASAP',
                '5' => 'Broken',
                '6' => 'Planned',
            ];

            $snapQuery = DB::table('od_appointment_schedule_snapshots')
                ->where('office_id', $officeId)
                ->whereBetween('snapshot_date', [$start, $end]);

            if ($provNum) {
                $snapQuery->where('prov_num', $provNum);
            } elseif ($clinicNum) {
                $snapQuery->where('clinic_num', $clinicNum);
            }

            if ($snapQuery->exists()) {
                $snapAppts = $snapQuery->orderBy('apt_date_time')->get()->unique('pat_num');
                $patMap = $mapPatients($snapAppts->pluck('pat_num')->unique());

                foreach ($snapAppts as $apt) {
                    $provInfo = $formatProv($apt->prov_num);
                    $st = (string) $apt->apt_status;
                    $rows[] = [
                        'pat_id' => $apt->pat_num,
                        'patient' => ['label' => $patMap[$apt->pat_num] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => $apt->apt_num,
                        'appt_date' => $apt->apt_date_time ? date('M d, Y h:i A', strtotime($apt->apt_date_time)) : '—',
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'description' => $apt->proc_descript ?: 'Appointment',
                    ];
                }
                $totals = [];
            } else {
                $apptsQuery = DB::table('od_appointments')
                    ->select('AptNum', 'PatNum', 'ProvNum', 'AptDateTime', 'AptStatus', 'ProcDescript')
                    ->whereNotIn('AptStatus', [6])
                    ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

                if ($provNum) {
                    $apptsQuery->where('ProvNum', $provNum);
                } elseif ($clinicNum) {
                    $apptsQuery->where('ClinicNum', $clinicNum);
                }

                $appts = $apptsQuery->orderBy('AptDateTime')->get()->unique('PatNum');
                $patMap = $mapPatients($appts->pluck('PatNum')->unique());

                foreach ($appts as $apt) {
                    $provInfo = $formatProv($apt->ProvNum);
                    $st = (string) $apt->AptStatus;
                    $rows[] = [
                        'pat_id' => $apt->PatNum,
                        'patient' => ['label' => $patMap[$apt->PatNum] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => $apt->AptNum,
                        'appt_date' => date('M d, Y h:i A', strtotime($apt->AptDateTime)),
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'description' => $apt->ProcDescript ?: 'Appointment',
                    ];
                }
                $totals = [];
            }

        } elseif ($metric === 'sched_new_pts_visit') {
            $title = 'Scheduled New Patient Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'appt_id', 'label' => 'Appt ID', 'type' => 'text'],
                ['key' => 'appt_date', 'label' => 'Appt Date / Time', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
            ];

            $statusLabels = [
                '1' => 'Scheduled',
                '2' => 'Complete',
            ];

            $snapQuery = DB::table('od_appointment_schedule_snapshots')
                ->where('office_id', $officeId)
                ->where('is_new_patient', true)
                ->whereIn('apt_status', [1, 2])
                ->whereBetween('snapshot_date', [$start, $end]);

            if ($provNum) {
                $snapQuery->where('prov_num', $provNum);
            } elseif ($clinicNum) {
                $snapQuery->where('clinic_num', $clinicNum);
            }

            if ($snapQuery->exists()) {
                $snapAppts = $snapQuery->orderBy('apt_date_time')->get()->unique('pat_num');
                $patMap = $mapPatients($snapAppts->pluck('pat_num')->unique());

                foreach ($snapAppts as $apt) {
                    $provInfo = $formatProv($apt->prov_num);
                    $st = (string) $apt->apt_status;
                    $rows[] = [
                        'pat_id' => $apt->pat_num,
                        'patient' => ['label' => $patMap[$apt->pat_num] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => $apt->apt_num,
                        'appt_date' => $apt->apt_date_time ? date('M d, Y h:i A', strtotime($apt->apt_date_time)) : '—',
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'description' => $apt->proc_descript ?: 'New Patient Appointment',
                    ];
                }
                $totals = [];
            } else {
                $apptsQuery = DB::table('od_appointments')
                    ->select('AptNum', 'PatNum', 'ProvNum', 'AptDateTime', 'AptStatus', 'ProcDescript')
                    ->where('IsNewPatient', 1)
                    ->whereIn('AptStatus', [1, 2])
                    ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

                if ($provNum) {
                    $apptsQuery->where('ProvNum', $provNum);
                } elseif ($clinicNum) {
                    $apptsQuery->where('ClinicNum', $clinicNum);
                }

                $appts = $apptsQuery->orderBy('AptDateTime')->get()->unique('PatNum');
                $patMap = $mapPatients($appts->pluck('PatNum')->unique());

                foreach ($appts as $apt) {
                    $provInfo = $formatProv($apt->ProvNum);
                    $st = (string) $apt->AptStatus;
                    $rows[] = [
                        'pat_id' => $apt->PatNum,
                        'patient' => ['label' => $patMap[$apt->PatNum] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => $apt->AptNum,
                        'appt_date' => date('M d, Y h:i A', strtotime($apt->AptDateTime)),
                        'status' => $statusLabels[$st] ?? ('Status '.$st),
                        'description' => $apt->ProcDescript ?: 'New Patient Appointment',
                    ];
                }
                $totals = [];
            }

        } elseif ($metric === 'unscheduled_tx') {
            $title = 'Unscheduled Treatment Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'appt_id', 'label' => 'Appt ID', 'type' => 'text'],
                ['key' => 'appt_date', 'label' => 'Appt Date / Time', 'type' => 'text'],
                ['key' => 'unscheduled_tx', 'label' => 'Unsched Tx $', 'type' => 'money', 'agg' => 'sum'],
            ];

            $snapQuery = DB::table('od_appointment_schedule_snapshots')
                ->where('office_id', $officeId)
                ->whereBetween('snapshot_date', [$start, $end])
                ->where('unscheduled_tx', '>', 0);

            if ($provNum) {
                $snapQuery->where('prov_num', $provNum);
            } elseif ($clinicNum) {
                $snapQuery->where('clinic_num', $clinicNum);
            }

            $totUnsched = 0;
            if ($snapQuery->exists()) {
                $snapAppts = $snapQuery->orderBy('apt_date_time')->get()->unique('pat_num');
                $patMap = $mapPatients($snapAppts->pluck('pat_num')->unique());
                foreach ($snapAppts as $apt) {
                    $amt = (float) $apt->unscheduled_tx;
                    $totUnsched += $amt;
                    $provInfo = $formatProv($apt->prov_num);
                    $rows[] = [
                        'pat_id' => $apt->pat_num,
                        'patient' => ['label' => $patMap[$apt->pat_num] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => $apt->apt_num,
                        'appt_date' => $apt->apt_date_time ? date('M d, Y h:i A', strtotime($apt->apt_date_time)) : '—',
                        'unscheduled_tx' => $amt,
                    ];
                }
                $totals = ['unscheduled_tx' => $totUnsched];
            } else {
                // Fallback: Query all TP procedures for the date range
                $unschedQuery = DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->whereIn('ProcStatus', [1, '1', 6, '6', 'TP'])
                    ->whereRaw('(AptNum IS NULL OR AptNum = 0)')
                    ->whereBetween('ProcDate', [$start, $end]);

                if ($provNum) {
                    $unschedQuery->where('ProvNum', $provNum);
                } elseif ($clinicNum) {
                    $unschedQuery->where('ClinicNum', $clinicNum);
                }

                $logs = $unschedQuery->get();
                $patMap = $mapPatients($logs->pluck('PatNum')->unique());

                foreach ($logs as $log) {
                    $fee = (float) $log->ProcFee;
                    $totUnsched += $fee;
                    $provInfo = $formatProv($log->ProvNum);
                    $rows[] = [
                        'pat_id' => $log->PatNum,
                        'patient' => ['label' => $patMap[$log->PatNum] ?? 'Unknown', 'link' => true],
                        'prov_id' => $provInfo['id'],
                        'provider' => $provInfo['name'],
                        'appt_id' => '—',
                        'appt_date' => date('M d, Y', strtotime($log->ProcDate)),
                        'unscheduled_tx' => $fee,
                    ];
                }
                $totals = ['unscheduled_tx' => $totUnsched];
            }

        } elseif ($metric === 'open_appt_hours') {
            $title = 'Open Appointment Hours Breakdown';
            $columns = [
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'sched_hours', 'label' => 'Provider Sched Hours', 'type' => 'number_2', 'agg' => 'sum'],
                ['key' => 'booked_hours', 'label' => 'Booked Appt Hours', 'type' => 'number_2', 'agg' => 'sum'],
                ['key' => 'open_hours', 'label' => 'Open Hours', 'type' => 'number_2', 'agg' => 'sum'],
            ];

            $schedQuery = DB::table('od_schedules')
                ->where('office_id', $officeId)
                ->where('SchedType', 1)
                ->whereBetween('SchedDate', [$start, $end]);

            if ($provNum) {
                $schedQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $schedQuery->where('ClinicNum', $clinicNum);
            }

            $schedHoursByProvDate = [];
            foreach ($schedQuery->get() as $s) {
                $pNum = (int) ($s->ProvNum ?? 0);
                $d = substr((string) $s->SchedDate, 0, 10);
                $startSec = strtotime('1970-01-01 '.(string) $s->StartTime);
                $stopSec = strtotime('1970-01-01 '.(string) $s->StopTime);
                $mins = max(0, ($stopSec - $startSec) / 60);
                $k = $pNum.'|'.$d;
                $schedHoursByProvDate[$k] = ($schedHoursByProvDate[$k] ?? 0) + $mins;
            }

            $apptQuery = DB::table('od_appointments')
                ->where('office_id', $officeId)
                ->whereIn('AptStatus', [1, 2])
                ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $apptQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $apptQuery->where('ClinicNum', $clinicNum);
            }

            $bookedMinsByProvDate = [];
            foreach ($apptQuery->get() as $apt) {
                $pNum = (int) ($apt->ProvNum ?? 0);
                $d = substr((string) $apt->AptDateTime, 0, 10);
                $pattern = (string) ($apt->Pattern ?? '');
                $duration = strlen($pattern) > 0 ? strlen($pattern) * 5 : 60;
                $k = $pNum.'|'.$d;
                $bookedMinsByProvDate[$k] = ($bookedMinsByProvDate[$k] ?? 0) + $duration;
            }

            $allKeys = array_unique(array_merge(array_keys($schedHoursByProvDate), array_keys($bookedMinsByProvDate)));
            sort($allKeys);

            $totSched = 0;
            $totBooked = 0;
            $totOpen = 0;

            foreach ($allKeys as $k) {
                [$pNum, $d] = explode('|', $k);
                $sH = round(($schedHoursByProvDate[$k] ?? 0) / 60, 2);
                $bH = round(($bookedMinsByProvDate[$k] ?? 0) / 60, 2);
                $oH = max(0, round($sH - $bH, 2));

                $totSched += $sH;
                $totBooked += $bH;
                $totOpen += $oH;

                $provInfo = $formatProv((int) $pNum);

                $rows[] = [
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'date' => date('l - M d, Y', strtotime($d)),
                    'sched_hours' => number_format($sH, 2),
                    'booked_hours' => number_format($bH, 2),
                    'open_hours' => number_format($oH, 2),
                ];
            }

            $totals = [
                'sched_hours' => number_format($totSched, 2),
                'booked_hours' => number_format($totBooked, 2),
                'open_hours' => number_format($totOpen, 2),
            ];

        } elseif ($metric === 'gross') {
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

            $wosQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'ProvNum', 'ProcDate as AdjDate', 'WriteOff')
                ->whereBetween('ProcDate', [$start, $end])
                ->where('WriteOff', '!=', 0);

            if ($provNum) {
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
            }

            $adjs = $adjsQuery->get();
            $wos = $wosQuery->get();

            $patMap = $mapPatients($adjs->pluck('PatNum')->merge($wos->pluck('PatNum'))->unique());
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

            foreach ($wos as $wo) {
                $amt = -(float) $wo->WriteOff;
                if ($amt == 0) {
                    continue;
                }
                $totalAdj += $amt;

                $row = [
                    'pat_id' => $wo->PatNum,
                    'patient' => [
                        'label' => $patMap[$wo->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'date' => date('M d, Y', strtotime($wo->AdjDate)),
                    'adj_type' => 'WriteOff',
                    'adj_amt' => $amt,
                ];
                if (! $provNum) {
                    $row['prov_id'] = $wo->ProvNum;
                    $row['provider'] = ['label' => $provMap[$wo->ProvNum] ?? 'Unknown', 'link' => true];
                }

                if ($metric === 'adj_production') {
                    $gross = (float) DB::table('od_procedure_logs')
                        ->where('PatNum', $wo->PatNum)
                        ->where('ProcDate', 'like', substr($wo->AdjDate, 0, 10).'%')
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

        } elseif ($metric === 'collection' || $metric === 'actual_collection') {
            $title = $metric === 'actual_collection' ? 'Actual Collection Breakdown' : 'Collection Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'collection', 'label' => 'Collection', 'type' => 'money', 'agg' => 'sum'],
            ];

            $splitsQuery = DB::table('od_pay_splits as s')
                ->leftJoin('od_payments as p', 'p.PayNum', '=', 's.PayNum')
                ->select('s.PatNum', 's.ProvNum', 's.DatePay', 's.SplitAmt', 'p.PayType')
                ->whereBetween('s.DatePay', [$start.' 00:00:00', $end.' 23:59:59']);

            $claimsQuery = DB::table('od_claim_procs as cp')
                ->leftJoin('od_claim_payments as cpay', 'cpay.ClaimPaymentNum', '=', 'cp.ClaimPaymentNum')
                ->select('cp.PatNum', 'cp.ProvNum', 'cp.DateCP as DatePay', 'cp.InsPayAmt as SplitAmt', 'cpay.CarrierName')
                ->whereBetween('cp.DateCP', [$start.' 00:00:00', $end.' 23:59:59'])
                ->where('cp.Status', '!=', 0)
                ->where('cp.InsPayAmt', '!=', 0);

            if ($provNum) {
                $splitsQuery->where('s.ProvNum', $provNum);
                $claimsQuery->where('cp.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $splitsQuery->where('s.ClinicNum', $clinicNum);
                $claimsQuery->where('cp.ClinicNum', $clinicNum);
            }

            $splits = $splitsQuery->get();
            $claims = $claimsQuery->get();

            $allPatIds = $splits->pluck('PatNum')->merge($claims->pluck('PatNum'))->filter()->unique();
            $patMap = $mapPatients($allPatIds);

            $totalCol = 0;
            foreach ($splits as $sp) {
                $amt = (float) $sp->SplitAmt;
                if ($amt == 0) {
                    continue;
                }
                $totalCol += $amt;
                $provInfo = $formatProv($sp->ProvNum);

                $r = [
                    'date' => date('M d, Y', strtotime($sp->DatePay)),
                    'pat_id' => $sp->PatNum,
                    'patient' => [
                        'label' => $patMap[$sp->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'collection' => $amt,
                    'amount' => $amt,
                ];
                $rows[] = $r;
            }

            foreach ($claims as $cp) {
                $amt = (float) $cp->SplitAmt;
                if ($amt == 0) {
                    continue;
                }
                $totalCol += $amt;
                $provInfo = $formatProv($cp->ProvNum);

                $r = [
                    'date' => date('M d, Y', strtotime($cp->DatePay)),
                    'pat_id' => $cp->PatNum,
                    'patient' => [
                        'label' => $patMap[$cp->PatNum] ?? 'Insurance Payment',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'collection' => $amt,
                    'amount' => $amt,
                ];
                $rows[] = $r;
            }
            $totals = ['collection' => $totalCol, 'amount' => $totalCol];

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
            $insSplitsQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'ProvNum', 'DateCP as DatePay', 'InsPayAmt as SplitAmt')
                ->whereBetween('DateCP', [$start, $end])
                ->where('Status', '!=', 0);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
                $splitsQuery->where('ProvNum', $provNum);
                $insSplitsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
                $splitsQuery->where('ClinicNum', $clinicNum);
                $insSplitsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();
            $adjs = $adjsQuery->get();
            $wos = $wosQuery->get();
            $splits = $metric === 'coll_pct' ? $splitsQuery->get()->merge($insSplitsQuery->get()) : [];

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
                ->whereBetween('a.AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $query->where('a.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('a.ClinicNum', $clinicNum);
            }

            $appts = $query->get()->unique('AptNum');

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

        } elseif ($metric === 'total_appointments' || $metric === 'total_appointments_count') {
            $title = 'Total Appointments Count Breakdown';
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
                    'a.AptStatus',
                    'a.AptDateTime',
                    'a.Note',
                    'a.ProcDescript'
                )
                ->whereBetween('a.AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);

            if ($provNum) {
                $query->where('a.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('a.ClinicNum', $clinicNum);
            }

            $appts = $query->orderBy('a.AptDateTime', 'desc')->get()->unique('AptNum');

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

            $statusMap = [
                '1' => 'Scheduled',
                '2' => 'Complete',
                '3' => 'Unscheduled',
                '4' => 'ASAP',
                '5' => 'Cancellation',
                '6' => 'Planned',
                '7' => 'PtNote',
                '8' => 'PtNoteCompleted',
            ];

            $totAmt = 0;
            foreach ($appts as $apt) {
                $p = $providers[$apt->ProvNum] ?? null;
                $provName = $p ? trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? '')) : ($provMap[$apt->ProvNum] ?? 'Unknown');
                $provAbbr = $p ? ($p->Abbr ?? '') : '';
                $provIdStr = $apt->ProvNum.($provAbbr ? ' - '.strtoupper($provAbbr) : '');

                $amt = (float) ($fees[$apt->AptNum] ?? 0);
                $totAmt += $amt;

                $noteText = trim(($apt->Note ?: $apt->ProcDescript) ?: 'No note');
                $aptType = $statusMap[(string) $apt->AptStatus] ?? ($apt->AptStatus ? 'Status '.$apt->AptStatus : 'Appointment');

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
                    'type' => $aptType,
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
                ->whereRaw("COALESCE(CodeNum, '') != '626'")
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $query->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('ClinicNum', $clinicNum);
            }

            $logs = $query->groupBy('ProcDate')
                ->havingRaw('SUM(ProcFee) > 0')
                ->orderBy('ProcDate', 'desc')
                ->get();

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
                ->when(! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $clinicName = $clinicNum ? $this->clinics->name((int) $clinicNum) : 'All Offices';
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
        } elseif ($metric === 'new_patient_prod') {
            $title = 'New Patient Prod Breakdown';
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
                ->when(! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0, fn ($q) => $q->where('pl.ClinicNum', $clinicNum))
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

            $clinicNums = (! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0) ? [$clinicNum] : [];
            $nptVisits = $this->patientVisits->newPatientVisits($start, $end, $clinicNums);
            $totalProduction = 0;
            foreach ($nptVisits as $visit) {
                $totalProduction += (float) $visit['amount'];
                $provLabel = ! empty($visit['prov_num']) && ! empty($provMap[$visit['prov_num']])
                    ? $provMap[$visit['prov_num']]
                    : 'N/A';
                $rows[] = [
                    'pat_id' => $visit['patient_id'],
                    'patient' => [
                        'label' => $visit['patient_name'] ?: 'Unknown',
                        'link' => true,
                    ],
                    'first_visit' => date('M d, Y', strtotime($visit['dates'])),
                    'providers' => $provLabel,
                    'insurance_carrier' => 'N/A',
                    'referral_source' => 'N/A',
                    'service_codes' => $visit['service_codes'],
                    'production' => $visit['amount'],
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

            $startWindow = date('Y-m-d', strtotime('-24 months', strtotime($start))).' 00:00:00';

            $firstVisitSubQ = $this->patients->firstVisitCohort();

            $activePts = DB::table('od_procedure_logs as pl')
                ->joinSub($firstVisitSubQ, 'fv', 'pl.PatNum', '=', 'fv.PatNum')
                ->select('pl.PatNum', 'fv.first_date')
                ->when(! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0, fn ($q) => $q->where('pl.ClinicNum', $clinicNum))
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
                    ->whereIn('AptStatus', [1, 2])
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

            $startWindow = date('Y-m-d', strtotime('-24 months', strtotime($start))).' 00:00:00';

            $logs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate')
                ->when(! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$startWindow, $end.' 23:59:59'])
                ->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $patData = [];
            foreach ($logs as $log) {
                $d = substr($log->ProcDate, 0, 10);
                $patNum = (int) $log->PatNum;
                if (! isset($patData[$patNum])) {
                    $patData[$patNum] = [
                        'days' => [],
                        'provs' => [],
                    ];
                }
                $patData[$patNum]['days'][$d] = true;
                if ($log->ProvNum) {
                    $patData[$patNum]['provs'][$log->ProvNum] = true;
                }
            }

            $totalVisits = 0;
            foreach ($patData as $patNum => $data) {
                $count = count($data['days']);
                $totalVisits += $count;

                $provNums = array_keys($data['provs']);
                sort($provNums, SORT_NUMERIC);

                $provAbbrs = [];
                $provNames = [];
                foreach ($provNums as $pNum) {
                    $p = $providers->get($pNum);
                    if ($p && ! empty($p->Abbr)) {
                        $provAbbrs[] = strtoupper(trim($p->Abbr));
                    }
                    $name = $resolveProvName($pNum);
                    if ($name) {
                        $provNames[] = $name;
                    }
                }
                sort($provAbbrs);
                $provNames = array_unique($provNames);
                sort($provNames);

                $providerIdStr = ! empty($provAbbrs)
                    ? implode(', ', $provNums).' - '.implode(', ', $provAbbrs)
                    : implode(', ', $provNums);

                $providersStr = implode(' | ', $provNames);

                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'provider_ids' => $providerIdStr,
                    'providers' => $providersStr,
                    'visits' => $count,
                ];
            }
            $totals = ['visits' => $totalVisits];

        } elseif ($metric === 'retention') {
            $title = 'Retention Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'first_visit', 'label' => 'First Ever Visit', 'type' => 'text'],
                ['key' => 'last_visit', 'label' => 'Recent Visit (0-18m)', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Patient Type', 'type' => 'text'],
            ];

            $start18m = date('Y-m-d', strtotime('-18 months', strtotime($end)));
            $start36m = date('Y-m-d', strtotime('-36 months', strtotime($end)));

            $firstProcs = DB::table('od_procedure_logs as pl')
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, '') != '626'")
                ->selectRaw('pl.PatNum, MIN(pl.ProcDate) as first_date')
                ->groupBy('pl.PatNum')
                ->pluck('first_date', 'PatNum')
                ->all();

            $patsAll = DB::table('od_procedure_logs as pl')
                ->selectRaw('pl.PatNum, MAX(pl.ProcDate) as last_date')
                ->when(! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0, fn ($q) => $q->where('pl.ClinicNum', $clinicNum))
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, '') != '626'")
                ->whereBetween('pl.ProcDate', [$start36m.' 00:00:00', $end.' 23:59:59'])
                ->groupBy('pl.PatNum')
                ->get();

            $patMap = $mapPatients($patsAll->pluck('PatNum')->unique());

            foreach ($patsAll as $pt) {
                $fDate = isset($firstProcs[$pt->PatNum]) ? substr($firstProcs[$pt->PatNum], 0, 10) : null;
                $lastDate = substr($pt->last_date, 0, 10);
                $isRecent = $lastDate >= $start18m && $lastDate <= $end;
                $isNew = $fDate && $fDate >= $start18m && $fDate <= $end;

                if ($isRecent && ! $isNew) {
                    $status = 'Retained Patient';
                } elseif ($isNew) {
                    $status = 'New Patient';
                } else {
                    $status = 'Inactive / Lost';
                }

                $rows[] = [
                    'pat_id' => $pt->PatNum,
                    'patient' => [
                        'label' => $patMap[$pt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'first_visit' => $fDate ? date('M d, Y', strtotime($fDate)) : '—',
                    'last_visit' => date('M d, Y', strtotime($pt->last_date)),
                    'status' => $status,
                ];
            }
            $totals = [];
        } elseif ($metric === 'actual_production') {
            $title = 'Actual Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $logsQuery = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            $adjsQuery = DB::table('od_adjustments')
                ->select('PatNum', 'ProvNum', 'AdjDate', 'AdjAmt')
                ->whereBetween('AdjDate', [$start, $end]);

            $wosQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'WriteOff')
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();
            $adjs = $adjsQuery->get();
            $wos = $wosQuery->get();

            $allPatIds = $logs->pluck('PatNum')->merge($adjs->pluck('PatNum'))->merge($wos->pluck('PatNum'))->filter()->unique();
            $patMap = $mapPatients($allPatIds);

            $map = [];
            foreach ($logs as $l) {
                $k = $l->PatNum.'|'.$l->ProvNum;
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $l->PatNum, 'prov' => $l->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0];
                }
                $map[$k]['gross'] += (float) $l->ProcFee;
            }
            foreach ($adjs as $a) {
                $k = $a->PatNum.'|'.$a->ProvNum;
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $a->PatNum, 'prov' => $a->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0];
                }
                $map[$k]['adj'] += (float) $a->AdjAmt;
            }
            foreach ($wos as $w) {
                $k = $w->PatNum.'|'.$w->ProvNum;
                if (! isset($map[$k])) {
                    $map[$k] = ['pat' => $w->PatNum, 'prov' => $w->ProvNum, 'gross' => 0, 'adj' => 0, 'wo' => 0];
                }
                $map[$k]['wo'] += (float) $w->WriteOff;
            }

            $totalNet = 0;
            foreach ($map as $m) {
                $net = $this->production->netFrom((float) $m['gross'], (float) $m['adj'], (float) $m['wo']);
                if (round($net, 2) == 0) {
                    continue;
                }
                $totalNet += $net;
                $provInfo = $formatProv($m['prov']);

                $rows[] = [
                    'pat_id' => $m['pat'],
                    'patient' => [
                        'label' => $patMap[$m['pat']] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'production' => $net,
                ];
            }
            $totals = ['production' => $totalNet];

        } elseif ($metric === 'actual_pts_visit') {
            $title = 'Actual Pts Visits Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'gross', 'label' => 'Gross production', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'adjustment', 'label' => 'Adjustment', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'writeoff', 'label' => 'Writeoff', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'visited', 'label' => 'Visited', 'type' => 'number', 'agg' => 'sum'],
                ['key' => 'production', 'label' => 'Production ($)', 'type' => 'money', 'agg' => 'sum'],
            ];

            $logsQuery = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->get();
            $patIds = $logs->pluck('PatNum')->unique()->filter()->values();
            $patMap = $mapPatients($patIds);

            $adjsQuery = DB::table('od_adjustments')
                ->select('PatNum', 'AdjAmt')
                ->whereIn('PatNum', $patIds)
                ->whereBetween('AdjDate', [$start, $end]);

            $wosQuery = DB::table('od_claim_procs')
                ->select('PatNum', 'WriteOff')
                ->whereIn('PatNum', $patIds)
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $adjsQuery->where('ProvNum', $provNum);
                $wosQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $adjsQuery->where('ClinicNum', $clinicNum);
                $wosQuery->where('ClinicNum', $clinicNum);
            }

            $adjs = $patIds->isNotEmpty() ? $adjsQuery->get() : collect();
            $wos = $patIds->isNotEmpty() ? $wosQuery->get() : collect();

            $patData = [];
            foreach ($logs as $l) {
                $patNum = (int) $l->PatNum;
                $fee = (float) $l->ProcFee;
                $d = substr((string) $l->ProcDate, 0, 10);

                if (! isset($patData[$patNum])) {
                    $patData[$patNum] = [
                        'pat_id' => $patNum,
                        'gross' => 0.0,
                        'adj' => 0.0,
                        'wo' => 0.0,
                        'days' => [],
                    ];
                }
                $patData[$patNum]['gross'] += $fee;
                $patData[$patNum]['days'][$d] = true;
            }

            foreach ($adjs as $a) {
                $patNum = (int) $a->PatNum;
                if (isset($patData[$patNum])) {
                    $patData[$patNum]['adj'] += (float) $a->AdjAmt;
                }
            }

            foreach ($wos as $w) {
                $patNum = (int) $w->PatNum;
                if (isset($patData[$patNum])) {
                    $patData[$patNum]['wo'] += (float) $w->WriteOff;
                }
            }

            $totGross = 0;
            $totAdj = 0;
            $totWo = 0;
            $totVisited = 0;
            $totProd = 0;

            foreach ($patData as $patNum => $data) {
                $gross = $data['gross'];
                $adj = $data['adj'];
                $wo = $data['wo'];
                $visited = count($data['days']);
                $net = $this->production->netFrom($gross, $adj, $wo);

                $totGross += $gross;
                $totAdj += $adj;
                $totWo += $wo;
                $totVisited += $visited;
                $totProd += $net;

                $rows[] = [
                    'pat_id' => $patNum,
                    'patient' => [
                        'label' => $patMap[$patNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'gross' => $gross,
                    'adjustment' => $adj,
                    'writeoff' => $wo,
                    'visited' => $visited,
                    'production' => $net,
                ];
            }

            $totals = [
                'gross' => $totGross,
                'adjustment' => $totAdj,
                'writeoff' => $totWo,
                'visited' => $totVisited,
                'production' => $totProd,
            ];

        } elseif ($metric === 'working_days') {
            $title = 'Working Days Breakdown';
            $columns = [
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $logsQuery = DB::table('od_procedure_logs')
                ->select(
                    DB::raw('DATE(ProcDate) as proc_date'),
                    'ProvNum',
                    DB::raw('SUM(ProcFee) as total_prod')
                )
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if ($provNum) {
                $logsQuery->where('ProvNum', $provNum);
            } elseif ($clinicNum) {
                $logsQuery->where('ClinicNum', $clinicNum);
            }

            $logs = $logsQuery->groupBy(DB::raw('DATE(ProcDate)'), 'ProvNum')
                ->havingRaw('SUM(ProcFee) > 0')
                ->orderBy('proc_date')
                ->get();

            $totProd = 0;
            foreach ($logs as $l) {
                $fee = (float) $l->total_prod;
                $totProd += $fee;
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'date' => date('M d, Y', strtotime($l->proc_date)),
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'prov_num' => $l->ProvNum,
                    'production' => $fee,
                ];
            }
            $totals = ['production' => $totProd];

        } elseif ($metric === 'open_appt_hours') {
            $title = 'Open Appointment Hours Breakdown';
            $columns = [
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'sched_hours', 'label' => 'Scheduled Hours', 'type' => 'number_2', 'agg' => 'sum'],
                ['key' => 'booked_hours', 'label' => 'Booked Hours', 'type' => 'number_2', 'agg' => 'sum'],
                ['key' => 'open_hours', 'label' => 'Open Hours', 'type' => 'number_2', 'agg' => 'sum'],
            ];

            $schedQuery = DB::table('od_schedules')
                ->select('ProvNum', 'SchedDate', 'StartTime', 'StopTime')
                ->where('SchedType', 1)
                ->whereBetween('SchedDate', [$start, $end]);
            if ($clinicNum) {
                $schedQuery->where('ClinicNum', $clinicNum);
            }
            $scheds = $schedQuery->get();

            $apptQuery = DB::table('od_appointments')
                ->select('ProvNum', 'AptDateTime', 'Pattern')
                ->whereIn('AptStatus', [1, 2])
                ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);
            if ($clinicNum) {
                $apptQuery->where('ClinicNum', $clinicNum);
            }
            $appts = $apptQuery->get();

            $schedMins = [];
            foreach ($scheds as $s) {
                $d = substr((string) $s->SchedDate, 0, 10);
                $pNum = (int) $s->ProvNum;
                $mins = max(0, (strtotime('1970-01-01 '.(string) $s->StopTime) - strtotime('1970-01-01 '.(string) $s->StartTime)) / 60);
                $k = $d.'|'.$pNum;
                $schedMins[$k] = ($schedMins[$k] ?? 0) + $mins;
            }

            $bookedMins = [];
            foreach ($appts as $apt) {
                $d = substr((string) $apt->AptDateTime, 0, 10);
                $pNum = (int) $apt->ProvNum;
                $pattern = (string) ($apt->Pattern ?? '');
                $dur = strlen($pattern) > 0 ? strlen($pattern) * 5 : 60;
                $k = $d.'|'.$pNum;
                $bookedMins[$k] = ($bookedMins[$k] ?? 0) + $dur;
            }

            $allKeys = array_unique(array_merge(array_keys($schedMins), array_keys($bookedMins)));
            $totSchedH = 0;
            $totBookedH = 0;
            $totOpenH = 0;

            foreach ($allKeys as $k) {
                [$d, $pNumStr] = explode('|', $k);
                $pNum = (int) $pNumStr;
                $sH = round(($schedMins[$k] ?? 0) / 60, 2);
                $bH = round(($bookedMins[$k] ?? 0) / 60, 2);
                $oH = max(0, round($sH - $bH, 2));

                $totSchedH += $sH;
                $totBookedH += $bH;
                $totOpenH += $oH;

                $provInfo = $formatProv($pNum);

                $rows[] = [
                    'date' => date('M d, Y', strtotime($d)),
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'sched_hours' => $sH,
                    'booked_hours' => $bH,
                    'open_hours' => $oH,
                ];
            }
            $totals = ['sched_hours' => $totSchedH, 'booked_hours' => $totBookedH, 'open_hours' => $totOpenH];

        } elseif ($metric === 'unscheduled_tx') {
            $title = 'Unscheduled Treatment Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'procedure', 'label' => 'Procedure', 'type' => 'text'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'agg' => 'sum'],
            ];

            $query = DB::table('od_procedure_logs as pl')
                ->leftJoin('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->select('pl.PatNum', 'pl.ProvNum', 'pl.ProcDate', 'pl.ProcFee', 'pc.ProcCode', 'pc.Descript')
                ->whereIn('pl.ProcStatus', [1, '1', 'TP'])
                ->whereRaw('(pl.AptNum IS NULL OR pl.AptNum = 0)')
                ->whereBetween('pl.ProcDate', [$start, $end]);

            if ($provNum) {
                $query->where('pl.ProvNum', $provNum);
            } elseif ($clinicNum) {
                $query->where('pl.ClinicNum', $clinicNum);
            }

            $logs = $query->get();
            $patMap = $mapPatients($logs->pluck('PatNum')->unique());

            $totAmt = 0;
            foreach ($logs as $l) {
                $amt = (float) $l->ProcFee;
                $totAmt += $amt;
                $provInfo = $formatProv($l->ProvNum);
                $procDesc = trim(($l->ProcCode ? $l->ProcCode.' - ' : '').($l->Descript ?? 'Procedure'));

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'procedure' => $procDesc,
                    'amount' => $amt,
                ];
            }
            $totals = ['amount' => $totAmt];

        } elseif ($metric === 'booked_production') {
            $title = 'Booked Production Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $completedLogs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $isCompleted = $completedLogs->isNotEmpty();
            $targetLogs = $isCompleted ? $completedLogs : DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereNotIn('ProcStatus', ProcStatus::completed())
                ->where('ProcFee', '>', 0)
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $patMap = $mapPatients($targetLogs->pluck('PatNum')->unique());
            $totProd = 0;
            $typeLabel = $isCompleted ? 'Actual' : 'Scheduled';

            foreach ($targetLogs as $l) {
                $fee = (float) $l->ProcFee;
                $totProd += $fee;
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => $typeLabel,
                    'production' => $fee,
                ];
            }
            $totals = ['production' => $totProd];

        } elseif ($metric === 'actual_prod_vs_goal') {
            $title = 'Actual Prod VS Goal Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Actual Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $logs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $patMap = $mapPatients($logs->pluck('PatNum')->unique());
            $totProd = 0;
            foreach ($logs as $l) {
                $fee = (float) $l->ProcFee;
                $totProd += $fee;
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'production' => $fee,
                ];
            }
            $totals = ['production' => $totProd];

        } elseif ($metric === 'actual_vs_sched_prod') {
            $title = 'Actual VS Sched Prod Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
                ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'agg' => 'sum'],
            ];

            $completedLogs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $schedLogs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate', 'ProcFee')
                ->whereNotIn('ProcStatus', ProcStatus::completed())
                ->where('ProcFee', '>', 0)
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $patMap = $mapPatients($completedLogs->pluck('PatNum')->merge($schedLogs->pluck('PatNum'))->unique());
            $totProd = 0;

            foreach ($completedLogs as $l) {
                $fee = (float) $l->ProcFee;
                $totProd += $fee;
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Actual',
                    'production' => $fee,
                ];
            }

            foreach ($schedLogs as $l) {
                $fee = (float) $l->ProcFee;
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Scheduled',
                    'production' => $fee,
                ];
            }
            $totals = ['production' => $totProd];

        } elseif ($metric === 'act_vs_sched_pts') {
            $title = 'Act VS Sched PTS Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ];

            $completedLogs = DB::table('od_procedure_logs')
                ->select('PatNum', 'ProvNum', 'ProcDate')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $appts = DB::table('od_appointments')
                ->select('PatNum', 'ProvNum', 'AptDateTime')
                ->whereIn('AptStatus', [1, 2])
                ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $patMap = $mapPatients($completedLogs->pluck('PatNum')->merge($appts->pluck('PatNum'))->unique());

            foreach ($completedLogs->unique('PatNum') as $l) {
                $provInfo = $formatProv($l->ProvNum);

                $rows[] = [
                    'pat_id' => $l->PatNum,
                    'patient' => [
                        'label' => $patMap[$l->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Actual Visit',
                ];
            }

            foreach ($appts->unique('PatNum') as $apt) {
                $provInfo = $formatProv($apt->ProvNum);

                $rows[] = [
                    'pat_id' => $apt->PatNum,
                    'patient' => [
                        'label' => $patMap[$apt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Scheduled Appt',
                ];
            }
            $totals = [];

        } elseif ($metric === 'act_vs_sched_npts') {
            $title = 'Act VS Sched NPTS Breakdown';
            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider Ids', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Providers', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ];

            $clinicNums = (! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0) ? [$clinicNum] : [];
            $nptVisits = $this->patientVisits->newPatientVisits($start, $end, $clinicNums);

            $schedNptAppts = DB::table('od_appointments')
                ->select('PatNum', 'ProvNum', 'AptDateTime')
                ->where('IsNewPatient', 1)
                ->whereIn('AptStatus', [1, 2])
                ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
                ->when($provNum, fn ($q) => $q->where('ProvNum', $provNum))
                ->when($clinicNum, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->get();

            $patMap = $mapPatients(collect($nptVisits)->pluck('patient_id')->merge($schedNptAppts->pluck('PatNum'))->unique());

            foreach ($nptVisits as $visit) {
                $pNum = $visit['prov_num'] ?? 0;
                $provInfo = $formatProv($pNum);

                $rows[] = [
                    'pat_id' => $visit['patient_id'],
                    'patient' => [
                        'label' => $visit['patient_name'] ?: 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Actual New Patient',
                ];
            }

            foreach ($schedNptAppts as $apt) {
                $provInfo = $formatProv($apt->ProvNum);

                $rows[] = [
                    'pat_id' => $apt->PatNum,
                    'patient' => [
                        'label' => $patMap[$apt->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'type' => 'Scheduled New Patient',
                ];
            }
            $totals = [];
        } elseif ($metric === 'claims_day' || $metric === 'claims') {
            $clinicName = null;
            if (! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0) {
                $clinicName = $this->clinics->name((int) $clinicNum);
            }
            $dayLabel = date('M d, Y', strtotime($start));
            $title = ($clinicName ? ($clinicName.' - ') : '').'Claims & Daily Procedures ('.$dayLabel.')';

            $columns = [
                ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
                ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
                ['key' => 'prov_id', 'label' => 'Provider ID', 'type' => 'text'],
                ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'],
                ['key' => 'code', 'label' => 'Procedure Code', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'tooth', 'label' => 'Tooth', 'type' => 'text'],
                ['key' => 'surf', 'label' => 'Surf', 'type' => 'text'],
                ['key' => 'fee', 'label' => 'Fee ($)', 'type' => 'money', 'agg' => 'sum'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ];

            $procsQuery = DB::table('od_claim_procs as cp')
                ->join('od_procedure_logs as pl', 'cp.ProcNum', '=', 'pl.ProcNum')
                ->leftJoin('od_procedures as pc', 'pc.CodeNum', '=', 'pl.CodeNum')
                ->select(
                    'pl.ProcNum',
                    'pl.PatNum',
                    'pl.ProvNum',
                    'cp.ClinicNum',
                    'cp.ProcDate',
                    'pl.ProcFee',
                    'pl.ToothNum',
                    'pl.Surf',
                    'pl.ProcStatus',
                    'pc.ProcCode',
                    'pc.Descript'
                )
                ->whereBetween('cp.ProcDate', [$start, $end])
                ->whereIn('pl.ProcStatus', ProcStatus::completed());

            if (! empty($clinicNum) && $clinicNum !== '0' && $clinicNum != 0) {
                $procsQuery->where('cp.ClinicNum', $clinicNum);
            }
            if ($provNum) {
                $procsQuery->where('pl.ProvNum', $provNum);
            }

            $procs = $procsQuery->orderBy('pl.PatNum')->get();

            $patMap = $mapPatients($procs->pluck('PatNum')->unique());

            $totalFee = 0;
            foreach ($procs as $proc) {
                $fee = (float) $proc->ProcFee;
                $totalFee += $fee;
                $provInfo = $formatProv($proc->ProvNum);
                $code = $proc->ProcCode ?: 'Unknown';
                $desc = $proc->Descript ?: 'Procedure';

                $rows[] = [
                    'pat_id' => $proc->PatNum,
                    'patient' => [
                        'label' => $patMap[$proc->PatNum] ?? 'Unknown',
                        'link' => true,
                    ],
                    'prov_id' => $provInfo['id'],
                    'provider' => $provInfo['name'],
                    'code' => $code,
                    'description' => $desc,
                    'tooth' => $proc->ToothNum ?: '-',
                    'surf' => $proc->Surf ?: '-',
                    'fee' => $fee,
                    'status' => 'Complete',
                ];
            }

            $totals = ['fee' => $totalFee];
        }

        return view('components.app-components.drilldown.table-content', compact('title', 'columns', 'rows', 'totals', 'providerInfo'));
    }
}
