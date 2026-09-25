<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\LocationSelection;
use App\Models\OdProvider;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HygieneRecallController extends Controller
{
    /**
     * Jarvis standard base definition for Hygiene Service Codes.
     */
    public const HYGIENE_CODES = ['D1110', 'D1120', 'D4910', 'D4341', 'D4342', 'D4346', 'D4355'];

    public function __construct(
        private readonly ClinicRegistry $clinics,
    ) {}

    private function resolveLocations(Request $request): LocationSelection
    {
        if ($request->filled('locations')) {
            return $this->clinics->select($request->input('locations'));
        }

        if ($request->filled('clinic') && $request->input('clinic') !== 'all') {
            $clinicVal = $request->input('clinic');

            return $this->clinics->select($clinicVal);
        }

        if ($request->filled('office_id') || $request->filled('clinic_num') || $request->filled('clinic_id')) {
            $officeInput = $request->input('office_id');
            if ($officeInput === 'all') {
                return $this->clinics->select('all');
            }
            $officeId = ($officeInput !== null && $officeInput !== '') ? (int) $officeInput : Office::getActiveOfficeId();
            $clinicInput = $request->input('clinic_id') ?? $request->input('clinic_num');
            if ($clinicInput === null && $officeId !== null) {
                $clinicInput = $this->clinics->getActiveClinicNum($officeId);
            }
            if ($clinicInput !== null && $clinicInput !== '' && $clinicInput !== 'all') {
                return $this->clinics->select("{$officeId}:{$clinicInput}");
            }
            if ($officeId !== null) {
                return $this->clinics->select((string) $officeId);
            }
        }

        return $this->clinics->select(null);
    }

    /**
     * Display the Hygiene Recall page.
     */
    public function index()
    {
        $locations = $this->clinics->locations();
        $selectedLocations = $this->clinics->select(request('locations'))->keys();

        return view('hygiene-recall.index', compact('locations', 'selectedLocations'));
    }

    /**
     * Return JSON data for Hygiene Recall DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $selection = $this->resolveLocations($request);

        $rows = $this->computeRecallData($start, $end, $selection);

        $totalMissed = $rows->sum('raw.missed_recall');
        $totalRecalled = $rows->sum('raw.patient_recalled');
        $totalFutureApts = $rows->sum('raw.future_appointments');
        $totalDollars = $rows->sum('raw.patients_recalled_dollars');
        $totalDueAll = $totalMissed + $totalRecalled;
        $overallRate = $totalDueAll > 0 ? round(($totalRecalled / $totalDueAll) * 100, 2) : 0.0;

        $count = $rows->count();
        $avgMissed = $count > 0 ? round($totalMissed / $count, 1) : 0;
        $avgRecalled = $count > 0 ? round($totalRecalled / $count, 1) : 0;
        $avgFutureApts = $count > 0 ? round($totalFutureApts / $count, 1) : 0;
        $avgDollars = $count > 0 ? round($totalDollars / $count, 2) : 0.0;
        $avgRate = $count > 0 ? round($rows->avg('raw.patient_recall_rate'), 2) : 0.0;

        return response()->json([
            'draw' => (int) $request->get('draw', 1),
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $rows->values()->all(),
            'total' => [
                'missed_recall' => (int) $totalMissed,
                'patient_recalled' => (int) $totalRecalled,
                'future_appointments' => (int) $totalFutureApts,
                'patients_recalled_dollars' => '$ '.number_format($totalDollars, 2),
                'patient_recall_rate' => number_format($overallRate, 2).'%',
            ],
            'average' => [
                'missed_recall' => $avgMissed,
                'patient_recalled' => $avgRecalled,
                'future_appointments' => $avgFutureApts,
                'patients_recalled_dollars' => '$ '.number_format($avgDollars, 2),
                'patient_recall_rate' => number_format($avgRate, 2).'%',
            ],
        ]);
    }

    /**
     * AJAX endpoint for Hygiene Recall Drill-down modal.
     */
    public function drilldown(Request $request)
    {
        $metric = $request->get('metric', 'all');
        $provNum = $request->get('prov_num');
        $start = $request->get('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $selection = $this->resolveLocations($request);
        $scopes = $selection->scopes();

        $title = match ($metric) {
            'missed_recall' => 'Missed Recalls Breakdown',
            'patient_recalled' => 'Recalled Patients Breakdown',
            'future_appointments' => 'Future Recall Appointments Breakdown',
            'patients_recalled_dollars' => 'Recalled Production Breakdown',
            default => 'Hygiene Recall Patient Breakdown',
        };

        $provMap = OdProvider::all()->keyBy(fn ($p) => $p->office_id.'-'.$p->ProvNum);

        $providerInfo = null;
        if ($provNum && (int) $provNum > 0) {
            $matchedProv = OdProvider::where('ProvNum', (int) $provNum)->first();
            if ($matchedProv) {
                $name = trim(($matchedProv->LName ?? '').(($matchedProv->LName && $matchedProv->PName) ? ', ' : '').($matchedProv->PName ?? ''));
                $providerInfo = [
                    'name' => $name ?: 'Provider '.$matchedProv->ProvNum,
                    'id' => $matchedProv->ProvNum.($matchedProv->Abbr ? ' - '.$matchedProv->Abbr : ''),
                ];
            }
        }

        $columns = [
            ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
            ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ['key' => 'date_due', 'label' => 'Due Date', 'type' => 'text'],
            ['key' => 'recall_type', 'label' => 'Recall Type', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'future_apt', 'label' => 'Future Apt Date', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Future Fee', 'type' => 'money', 'agg' => 'sum'],
        ];

        if (! $provNum) {
            $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
        }
        $columns[] = ['key' => 'location', 'label' => 'Location', 'type' => 'text'];

        $rows = [];
        $totalFee = 0.0;

        foreach ($scopes as $officeId => $scopedClinics) {
            $hygieneTypeNums = $this->getHygieneRecallTypeNums($officeId);

            $query = DB::table('od_recalls as r')
                ->join('od_patients as p', function ($join) use ($officeId) {
                    $join->on('r.PatNum', '=', 'p.PatNum')
                        ->where('p.office_id', '=', $officeId);
                })
                ->leftJoin('od_recall_types as rt', function ($join) use ($officeId) {
                    $join->on('r.RecallTypeNum', '=', 'rt.RecallTypeNum')
                        ->where('rt.office_id', '=', $officeId);
                })
                ->where('r.office_id', $officeId)
                ->where(function ($q) {
                    $q->whereNull('r.IsDisabled')
                        ->orWhereIn('r.IsDisabled', ['false', '0', 0, false]);
                })
                ->whereIn('r.RecallTypeNum', $hygieneTypeNums)
                ->whereBetween('r.DateDue', [$start, $end]);

            if ($provNum && (int) $provNum > 0) {
                $query->where('p.PriProv', (int) $provNum);
            }

            if (! empty($scopedClinics)) {
                $query->whereIn('p.ClinicNum', $scopedClinics);
            }

            $recalls = $query->select([
                'r.RecallNum',
                'r.PatNum',
                'r.DateDue',
                'r.DatePrevious',
                'r.office_id',
                'p.ClinicNum',
                'rt.Description as RecallTypeName',
                'p.PriProv as ProvNum',
                'p.LName as PatLName',
                'p.FName as PatFName',
            ])->orderBy('r.DateDue', 'desc')->get();

            if ($recalls->isEmpty()) {
                continue;
            }

            $patNums = $recalls->pluck('PatNum')->unique();

            $futureApts = DB::table('od_appointments')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $patNums)
                ->whereDate('AptDateTime', '>=', Carbon::today()->toDateString())
                ->whereIn('AptStatus', [1, 2, 4])
                ->select(['AptNum', 'PatNum', 'AptDateTime', 'ClinicNum'])
                ->get()
                ->groupBy('PatNum');

            $futureAptNums = $futureApts->flatten(1)->pluck('AptNum')->filter()->unique();
            $futureAptFees = ! $futureAptNums->isEmpty()
                ? DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->whereIn('AptNum', $futureAptNums)
                    ->groupBy('AptNum')
                    ->selectRaw('AptNum, SUM(ProcFee) as total_fee')
                    ->pluck('total_fee', 'AptNum')
                : collect();

            foreach ($recalls as $r) {
                $apts = $futureApts->get($r->PatNum);
                $hasFuture = $apts && $apts->isNotEmpty();

                // Filter by metric
                if ($metric === 'missed_recall' && $hasFuture) {
                    continue;
                }
                if (($metric === 'patient_recalled' || $metric === 'future_appointments' || $metric === 'patients_recalled_dollars') && ! $hasFuture) {
                    continue;
                }

                $patFee = 0.0;
                $nextAptDate = '—';
                if ($hasFuture) {
                    $firstApt = $apts->sortBy('AptDateTime')->first();
                    $nextAptDate = Carbon::parse($firstApt->AptDateTime)->format('M d, Y g:i A');
                    foreach ($apts as $apt) {
                        $patFee += (float) ($futureAptFees->get($apt->AptNum) ?? 0.0);
                    }
                }

                $totalFee += $patFee;

                $patName = trim(($r->PatLName ?? '').(($r->PatLName && $r->PatFName) ? ', ' : '').($r->PatFName ?? ''));
                $provLookup = $provMap->get($officeId.'-'.$r->ProvNum);
                $provName = $provLookup
                    ? trim(($provLookup->LName ?? '').(($provLookup->LName && $provLookup->PName) ? ', ' : '').($provLookup->PName ?? ''))
                    : ($r->ProvNum ? 'Provider '.$r->ProvNum : 'Unassigned');

                $clinicNum = (int) ($r->ClinicNum ?? 0);
                $locName = $this->clinics->name($clinicNum, $officeId);

                $row = [
                    'pat_id' => $r->PatNum,
                    'patient' => [
                        'label' => $patName ?: 'Patient '.$r->PatNum,
                        'link' => true,
                    ],
                    'date_due' => $r->DateDue ? Carbon::parse($r->DateDue)->format('M d, Y') : '—',
                    'recall_type' => $r->RecallTypeName ?: 'Standard Recall',
                    'status' => $hasFuture ? 'Recalled (Scheduled)' : 'Missed (Unscheduled)',
                    'future_apt' => $nextAptDate,
                    'amount' => $patFee,
                ];

                if (! $provNum) {
                    $row['prov_id'] = $r->ProvNum;
                    $row['provider'] = [
                        'label' => $provName,
                        'link' => true,
                    ];
                }

                $row['location'] = $locName;

                $rows[] = $row;
            }
        }

        $totals = ['amount' => $totalFee];

        return view('components.app-components.drilldown.table-content', compact('title', 'columns', 'rows', 'totals', 'providerInfo'));
    }

    /**
     * CSV Export.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $start = $request->get('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $selection = $this->resolveLocations($request);

        $fileName = 'hygiene-recall-'.$start.'-to-'.$end.'.csv';

        return response()->streamDownload(function () use ($start, $end, $selection) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Provider',
                'Provider ID',
                'Office',
                'Missed Recall',
                'Patient Recalled',
                '# of Future Appointments',
                'Patients Recalled $',
                'Patient Recall Rate %',
            ]);

            $rows = $this->computeRecallData($start, $end, $selection);

            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['provider_name'],
                    $r['provider_id'],
                    $r['office'],
                    $r['missed_recall'],
                    $r['patient_recalled'],
                    $r['future_appointments'],
                    $r['patients_recalled_dollars'],
                    $r['patient_recall_rate'],
                ]);
            }

            if ($rows->isNotEmpty()) {
                $totalMissed = $rows->sum('raw.missed_recall');
                $totalRecalled = $rows->sum('raw.patient_recalled');
                $totalFutureApts = $rows->sum('raw.future_appointments');
                $totalDollars = $rows->sum('raw.patients_recalled_dollars');
                $totalDueAll = $totalMissed + $totalRecalled;
                $overallRate = $totalDueAll > 0 ? round(($totalRecalled / $totalDueAll) * 100, 2) : 0.0;

                fputcsv($handle, [
                    'Total:',
                    '',
                    '',
                    $totalMissed,
                    $totalRecalled,
                    $totalFutureApts,
                    '$ '.number_format($totalDollars, 2),
                    number_format($overallRate, 2).'%',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Compute recall rows aggregated by provider and clinic across selected locations.
     */
    protected function computeRecallData(string $start, string $end, LocationSelection|string|null $selection)
    {
        if (! ($selection instanceof LocationSelection)) {
            $selection = $this->clinics->select($selection);
        }

        $scopes = $selection->scopes();
        $result = collect();

        foreach ($scopes as $officeId => $scopedClinics) {
            $hygieneTypeNums = $this->getHygieneRecallTypeNums($officeId);

            $query = DB::table('od_recalls as r')
                ->join('od_patients as p', function ($join) use ($officeId) {
                    $join->on('r.PatNum', '=', 'p.PatNum')
                        ->where('p.office_id', '=', $officeId);
                })
                ->leftJoin('od_providers as prov', function ($join) use ($officeId) {
                    $join->on('p.PriProv', '=', 'prov.ProvNum')
                        ->where('prov.office_id', '=', $officeId);
                })
                ->where('r.office_id', $officeId)
                ->where(function ($q) {
                    $q->whereNull('r.IsDisabled')
                        ->orWhereIn('r.IsDisabled', ['false', '0', 0, false]);
                })
                ->whereIn('r.RecallTypeNum', $hygieneTypeNums)
                ->whereBetween('r.DateDue', [$start, $end]);

            if (! empty($scopedClinics)) {
                $query->whereIn('p.ClinicNum', $scopedClinics);
            }

            $recalls = $query->select([
                'r.RecallNum',
                'r.PatNum',
                'r.DateDue',
                'r.office_id',
                'p.ClinicNum',
                'p.PriProv as ProvNum',
                'prov.LName as ProvLName',
                'prov.PName as ProvFName',
                'prov.Abbr as ProvAbbr',
            ])->get();

            if ($recalls->isEmpty()) {
                continue;
            }

            $patNums = $recalls->pluck('PatNum')->unique();

            $futureApts = DB::table('od_appointments')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $patNums)
                ->whereDate('AptDateTime', '>=', Carbon::today()->toDateString())
                ->whereIn('AptStatus', [1, 2, 4])
                ->select(['AptNum', 'PatNum', 'AptDateTime', 'ClinicNum'])
                ->get()
                ->groupBy('PatNum');

            $futureAptNums = $futureApts->flatten(1)->pluck('AptNum')->filter()->unique();
            $futureAptFees = ! $futureAptNums->isEmpty()
                ? DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->whereIn('AptNum', $futureAptNums)
                    ->groupBy('AptNum')
                    ->selectRaw('AptNum, SUM(ProcFee) as total_fee')
                    ->pluck('total_fee', 'AptNum')
                : collect();

            // Group recalls by Provider, Office, and Clinic
            $grouped = $recalls->groupBy(function ($r) use ($officeId) {
                $clinicNum = (int) ($r->ClinicNum ?? 0);
                $provNum = (int) ($r->ProvNum ?? 0);

                return $provNum.'-'.$officeId.'-'.$clinicNum;
            });

            foreach ($grouped as $group) {
                $first = $group->first();
                $provNum = (int) ($first->ProvNum ?? 0);
                $clinicNum = (int) ($first->ClinicNum ?? 0);

                $provName = trim(($first->ProvLName ?? '').(($first->ProvLName && $first->ProvFName) ? ', ' : '').($first->ProvFName ?? ''));
                $provAbbr = $first->ProvAbbr ? substr($first->ProvAbbr, 0, 4) : 'PRV';
                $provIdStr = $provNum > 0 ? ($provNum.' - '.$provAbbr) : 'Unassigned';
                $officeName = $this->clinics->name($clinicNum, $officeId);

                $patsInGroup = $group->pluck('PatNum')->unique();
                $totalDue = $patsInGroup->count();

                $recalledPats = $patsInGroup->filter(function ($patNum) use ($futureApts) {
                    return $futureApts->has($patNum);
                });

                $patientRecalledCount = $recalledPats->count();
                $missedRecallCount = max(0, $totalDue - $patientRecalledCount);

                $futureAptCount = 0;
                $productionDollars = 0.0;

                foreach ($recalledPats as $patNum) {
                    $apts = $futureApts->get($patNum, collect());
                    $futureAptCount += $apts->count();
                    foreach ($apts as $apt) {
                        $productionDollars += (float) ($futureAptFees->get($apt->AptNum) ?? 0.0);
                    }
                }

                $recallRate = $totalDue > 0 ? round(($patientRecalledCount / $totalDue) * 100, 2) : 0.0;

                $result->push([
                    'prov_num' => $provNum,
                    'clinic_num' => $clinicNum > 0 ? $clinicNum : $officeId,
                    'office_id' => $officeId,
                    'provider_name' => $provName ?: ($provNum > 0 ? 'Provider '.$provNum : 'Unassigned Provider'),
                    'provider_id' => $provIdStr,
                    'office' => $officeName,
                    'missed_recall' => $missedRecallCount,
                    'patient_recalled' => $patientRecalledCount,
                    'future_appointments' => $futureAptCount,
                    'patients_recalled_dollars' => '$ '.number_format($productionDollars, 2),
                    'patient_recall_rate' => number_format($recallRate, 2).'%',
                    'raw' => [
                        'missed_recall' => $missedRecallCount,
                        'patient_recalled' => $patientRecalledCount,
                        'future_appointments' => $futureAptCount,
                        'patients_recalled_dollars' => $productionDollars,
                        'patient_recall_rate' => $recallRate,
                    ],
                ]);
            }
        }

        return $result->sortBy('provider_name')->values();
    }

    /**
     * Get valid hygiene RecallTypeNum values from Open Dental.
     *
     * @return int[]
     */
    protected function getHygieneRecallTypeNums(?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $types = DB::table('od_recall_types')
            ->where('office_id', $officeId)
            ->where(function ($q) {
                $q->whereIn('RecallTypeNum', [1, 2, 3])
                    ->orWhere('AppendToSpecial', 0)
                    ->orWhere('Description', 'LIKE', '%Prophy%')
                    ->orWhere('Description', 'LIKE', '%Perio%');
                foreach (self::HYGIENE_CODES as $code) {
                    $q->orWhere('Procedures', 'LIKE', "%{$code}%");
                }
            })
            ->pluck('RecallTypeNum')
            ->map(fn ($n) => (int) $n)
            ->toArray();

        return ! empty($types) ? $types : [1, 2, 3];
    }
}
