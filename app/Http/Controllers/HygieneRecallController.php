<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
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

    /**
     * Display the Hygiene Recall page.
     */
    public function index()
    {
        return view('hygiene-recall.index', [
            'clinics' => $this->clinics->all(Office::getActiveOfficeId()),
        ]);
    }

    /**
     * Return JSON data for Hygiene Recall DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $clinic = $request->get('clinic');

        $rows = $this->computeRecallData($start, $end, $clinic);

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
        $clinic = $request->get('clinic');
        $start = $request->get('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $officeId = ($clinic !== null && $clinic !== '' && $clinic !== 'all') ? (int) $clinic : Office::getActiveOfficeId();

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

        if ($provNum) {
            $query->where('p.PriProv', (int) $provNum);
        }

        $recalls = $query->select([
            'r.RecallNum',
            'r.PatNum',
            'r.DateDue',
            'r.DatePrevious',
            'r.office_id',
            'rt.Description as RecallTypeName',
            'p.PriProv as ProvNum',
            'p.LName as PatLName',
            'p.FName as PatFName',
        ])->orderBy('r.DateDue', 'desc')->get();

        $patNums = $recalls->pluck('PatNum')->unique();

        $futureApts = ! $patNums->isEmpty()
            ? DB::table('od_appointments')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $patNums)
                ->whereDate('AptDateTime', '>=', Carbon::today()->toDateString())
                ->whereIn('AptStatus', [1, 2, 4])
                ->select(['AptNum', 'PatNum', 'AptDateTime', 'ClinicNum'])
                ->get()
                ->groupBy('PatNum')
            : collect();

        $futureAptNums = $futureApts->flatten(1)->pluck('AptNum')->filter()->unique();
        $futureAptFees = ! $futureAptNums->isEmpty()
            ? DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('AptNum', $futureAptNums)
                ->groupBy('AptNum')
                ->selectRaw('AptNum, SUM(ProcFee) as total_fee')
                ->pluck('total_fee', 'AptNum')
            : collect();

        $provMap = OdProvider::all()->keyBy('ProvNum');

        $title = match ($metric) {
            'missed_recall' => 'Missed Recalls Breakdown',
            'patient_recalled' => 'Recalled Patients Breakdown',
            'future_appointments' => 'Future Recall Appointments Breakdown',
            'patients_recalled_dollars' => 'Recalled Production Breakdown',
            default => 'Hygiene Recall Patient Breakdown',
        };

        $providerInfo = null;
        if ($provNum) {
            $p = $provMap->get((int) $provNum);
            if ($p) {
                $name = trim(($p->LName ?? '').(($p->LName && $p->PName) ? ', ' : '').($p->PName ?? ''));
                $providerInfo = [
                    'name' => $name ?: 'Provider '.$p->ProvNum,
                    'id' => $p->ProvNum.($p->Abbr ? ' - '.$p->Abbr : ''),
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
        if (! $clinic || $clinic === 'all') {
            $columns[] = ['key' => 'location', 'label' => 'Location', 'type' => 'text'];
        }

        $rows = [];
        $totalFee = 0.0;

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
            $provider = $provMap->get((int) $r->ProvNum);
            $provName = $provider
                ? trim(($provider->LName ?? '').(($provider->LName && $provider->PName) ? ', ' : '').($provider->PName ?? ''))
                : 'Provider '.$r->ProvNum;

            $clinicNum = (int) ($r->office_id ?? 0);

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

            if (! $clinic || $clinic === 'all') {
                $row['location'] = $this->clinics->name($clinicNum, $officeId);
            }

            $rows[] = $row;
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
        $clinic = $request->get('clinic');

        $fileName = 'hygiene-recall-'.$start.'-to-'.$end.'.csv';

        return response()->streamDownload(function () use ($start, $end, $clinic) {
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

            $rows = $this->computeRecallData($start, $end, $clinic);

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
     * Compute recall rows aggregated by provider and clinic.
     */
    protected function computeRecallData(string $start, string $end, ?string $clinic)
    {
        $officeId = ($clinic !== null && $clinic !== '' && $clinic !== 'all') ? (int) $clinic : Office::getActiveOfficeId();
        $hygieneTypeNums = $this->getHygieneRecallTypeNums($officeId);

        $query = DB::table('od_recalls as r')
            ->join('od_patients as p', function ($join) use ($officeId) {
                $join->on('r.PatNum', '=', 'p.PatNum')
                    ->where('p.office_id', '=', $officeId);
            })
            ->join('od_providers as prov', function ($join) use ($officeId) {
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

        $recalls = $query->select([
            'r.RecallNum',
            'r.PatNum',
            'r.DateDue',
            'r.office_id',
            'p.PriProv as ProvNum',
            'prov.LName as ProvLName',
            'prov.PName as ProvFName',
            'prov.Abbr as ProvAbbr',
        ])->get();

        if ($recalls->isEmpty()) {
            return collect();
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

        // Group recalls by Provider and Clinic
        $grouped = $recalls->groupBy(function ($r) {
            $clinicId = (int) ($r->office_id ?? 0);

            return $r->ProvNum.'-'.$clinicId;
        });

        $result = collect();

        foreach ($grouped as $group) {
            $first = $group->first();
            $provNum = (int) $first->ProvNum;
            $clinicNum = (int) ($first->office_id ?? 0);

            $provName = trim(($first->ProvLName ?? '').(($first->ProvLName && $first->ProvFName) ? ', ' : '').($first->ProvFName ?? ''));
            $provAbbr = $first->ProvAbbr ? substr($first->ProvAbbr, 0, 4) : 'PRV';
            $provIdStr = $provNum.' - '.$provAbbr;
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
                'clinic_num' => $clinicNum,
                'provider_name' => $provName ?: 'Provider '.$provNum,
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
