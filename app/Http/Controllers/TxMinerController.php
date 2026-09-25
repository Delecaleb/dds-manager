<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\LocationSelection;
use App\Domain\Support\ProcStatus;
use App\Domain\TreatmentAcceptance\TreatmentAcceptanceService;
use App\Domain\TreatmentAcceptance\TxMinerPatientBreakdownService;
use App\Domain\TreatmentAcceptance\TxScheduling;
use App\Models\OdPatient;
use App\Models\OdProvider;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TxMinerController extends Controller
{
    public function index(ClinicRegistry $clinicRegistry)
    {
        $clinics = $clinicRegistry->all(Office::getActiveOfficeId());
        $providers = OdProvider::whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->get(['ProvNum', 'LName', 'PName', 'Abbr']);

        $lineOfBusinesses = [
            'Doctor',
            'Endo',
            'Hygiene',
            'Invisalign',
            'Oral Surgery',
            'Ortho',
            'Pedo',
            'Perio',
            'Prostho',
            'Others',
            'Not Set',
        ];

        return view('tx-miner.index', compact('clinics', 'providers', 'lineOfBusinesses'));
    }

    /**
     * By Month aggregated dataset.
     */
    public function data(Request $request, TreatmentAcceptanceService $txAcceptance, ClinicRegistry $clinicRegistry): JsonResponse
    {
        $draw = (int) $request->get('draw', 1);
        $start = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 20);

        $completed = ProcStatus::inList(ProcStatus::completed());
        $tp = ProcStatus::inList(ProcStatus::treatmentPlanned());
        $tpOrCompleted = ProcStatus::inList([...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()]);

        $monthGroupSql = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', pl.DateTP)"
            : "DATE_FORMAT(pl.DateTP, '%Y-%m')";

        $monthProcDateSql = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', pl.ProcDate)"
            : "DATE_FORMAT(pl.ProcDate, '%Y-%m')";

        $hasMonth = $request->filled('month');
        $hasCustomRange = ! $hasMonth && $request->filled('start_date') && $request->filled('end_date');

        if (! $hasCustomRange) {
            $monthInput = $request->input('month', now()->format('Y-m'));
            try {
                $targetMonth = Carbon::parse($monthInput);
            } catch (\Exception $e) {
                $targetMonth = now();
            }

            $startDate = $targetMonth->copy()->subMonths(12)->startOfMonth()->toDateString();
            $endDate = $targetMonth->copy()->endOfMonth()->toDateString();

            $monthList = [];
            for ($i = 0; $i <= 12; $i++) {
                $m = $targetMonth->copy()->subMonths($i)->format('Y-m');
                $monthList[$m] = (object) [
                    'month_group' => $m,
                    'total_tx_plan' => 0,
                    'tx_scheduled' => 0,
                    'tx_unscheduled' => 0,
                    'completed_tx' => 0,
                    'tx_presented_count' => 0,
                    'patients_seen' => 0,
                ];
            }

            $query = $this->baseQuery($request, $clinicRegistry, $startDate, $endDate)
                ->selectRaw("{$monthGroupSql} as month_group")
                ->tap(fn (Builder $q) => $this->selectScheduleSplit($q, $tp))
                ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$completed}) THEN pl.ProcFee ELSE 0 END) as completed_tx")
                ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpOrCompleted}) THEN pl.ProcFee ELSE 0 END) as total_tx_plan")
                ->selectRaw('COUNT(DISTINCT pl.PatNum) as tx_presented_count')
                ->groupBy('month_group')
                ->orderBy('month_group', 'desc');

            $dbRecords = $query->get();

            // Total unique patients seen in each month (completed procs with ProcDate in month)
            $patientsSeenMap = $this->scopeQuery($request, $clinicRegistry)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereNotNull('pl.ProcDate')
                ->where('pl.ProcDate', '!=', '0001-01-01')
                ->where('pl.ProcDate', '!=', '0000-00-00')
                ->whereBetween('pl.ProcDate', [$startDate, $endDate])
                ->selectRaw("{$monthProcDateSql} as month_group, COUNT(DISTINCT pl.PatNum) as patients_seen")
                ->groupBy('month_group')
                ->pluck('patients_seen', 'month_group');

            foreach ($dbRecords as $r) {
                if (isset($monthList[$r->month_group])) {
                    $r->patients_seen = (int) ($patientsSeenMap[$r->month_group] ?? 0);
                    $monthList[$r->month_group] = $r;
                }
            }

            foreach ($monthList as $mKey => $mObj) {
                if ($mObj->patients_seen === 0 && isset($patientsSeenMap[$mKey])) {
                    $mObj->patients_seen = (int) $patientsSeenMap[$mKey];
                }
            }

            $records = array_values($monthList);
            $totalRecords = count($records);
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = $this->baseQuery($request, $clinicRegistry)
                ->selectRaw("{$monthGroupSql} as month_group")
                ->tap(fn (Builder $q) => $this->selectScheduleSplit($q, $tp))
                ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$completed}) THEN pl.ProcFee ELSE 0 END) as completed_tx")
                ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpOrCompleted}) THEN pl.ProcFee ELSE 0 END) as total_tx_plan")
                ->selectRaw('COUNT(DISTINCT pl.PatNum) as tx_presented_count')
                ->groupBy('month_group')
                ->orderBy('month_group', 'desc');

            $patientsSeenMap = $this->scopeQuery($request, $clinicRegistry)
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereNotNull('pl.ProcDate')
                ->where('pl.ProcDate', '!=', '0001-01-01')
                ->where('pl.ProcDate', '!=', '0000-00-00')
                ->when($startDate && $endDate, fn ($q) => $q->whereBetween('pl.ProcDate', [$startDate, $endDate]))
                ->selectRaw("{$monthProcDateSql} as month_group, COUNT(DISTINCT pl.PatNum) as patients_seen")
                ->groupBy('month_group')
                ->pluck('patients_seen', 'month_group');

            $totalRecords = DB::query()->fromSub($query, 'sub')->count();
            $records = $query->skip($start)->take($length)->get();

            foreach ($records as $r) {
                $r->patients_seen = (int) ($patientsSeenMap[$r->month_group] ?? 0);
            }
        }

        $stagedRows = [];
        foreach ($records as $r) {
            $stagedRows[] = $this->mapRowMetrics($r, $txAcceptance) + [
                'month_group' => $r->month_group,
            ];
        }

        $heat = $this->computeHeatmapTiers($stagedRows);

        $data = [];
        foreach ($stagedRows as $row) {
            try {
                $monthLabel = Carbon::createFromFormat('Y-m', $row['month_group'])->format('F Y');
            } catch (\Exception $e) {
                $monthLabel = $row['month_group'];
            }

            $data[] = [
                'month' => $monthLabel,
                'month_group' => $row['month_group'],
                'total_tx_plan' => $this->formatCurrency($row['total_tx_plan_raw']),
                'tx_scheduled' => $this->formatCurrency($row['tx_scheduled_raw']),
                'tx_unscheduled' => $this->formatCurrency($row['tx_unscheduled_raw']),
                'completed_tx' => $this->formatCurrency($row['completed_tx_raw']),
                'case_acceptance' => $this->formatPercent($row['case_acceptance_raw']),
                'tx_presented' => $row['tx_presented_raw'],
                'avg_tx_plan' => $this->formatCurrency($row['avg_tx_plan_raw']),
                'patients_with_tx' => $this->formatPercent($row['patients_with_tx_raw']),
                'raw' => [
                    'total_tx_plan' => $row['total_tx_plan_raw'],
                    'tx_scheduled' => $row['tx_scheduled_raw'],
                    'tx_unscheduled' => $row['tx_unscheduled_raw'],
                    'completed_tx' => $row['completed_tx_raw'],
                    'case_acceptance' => $row['case_acceptance_raw'],
                    'tx_presented' => $row['tx_presented_raw'],
                    'avg_tx_plan' => $row['avg_tx_plan_raw'],
                    'patients_with_tx' => $row['patients_with_tx_raw'],
                ],
                'heat' => [
                    'total_tx_plan' => $this->getTierClass($heat, 'total_tx_plan', $row['total_tx_plan_raw']),
                    'tx_scheduled' => $this->getTierClass($heat, 'tx_scheduled', $row['tx_scheduled_raw']),
                    'tx_unscheduled' => $this->getTierClass($heat, 'tx_unscheduled', $row['tx_unscheduled_raw']),
                    'completed_tx' => $this->getTierClass($heat, 'completed_tx', $row['completed_tx_raw']),
                    'case_acceptance' => $this->getTierClass($heat, 'case_acceptance', $row['case_acceptance_raw']),
                    'tx_presented' => $this->getTierClass($heat, 'tx_presented', (float) $row['tx_presented_raw']),
                    'avg_tx_plan' => $this->getTierClass($heat, 'avg_tx_plan', $row['avg_tx_plan_raw']),
                    'patients_with_tx' => $this->getTierClass($heat, 'patients_with_tx', $row['patients_with_tx_raw']),
                ],
            ];
        }

        $summary = $this->computeSummaryRows($stagedRows, $txAcceptance);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
            'average' => $summary['average'],
            'total' => $summary['total'],
        ]);
    }

    /**
     * By Provider aggregated dataset.
     */
    public function dataProvider(Request $request, TreatmentAcceptanceService $txAcceptance, ClinicRegistry $clinicRegistry): JsonResponse
    {
        $draw = (int) $request->get('draw', 1);
        $start = (int) $request->get('start', 0);
        $length = $request->has('length') ? (int) $request->get('length') : -1;

        $completed = ProcStatus::inList(ProcStatus::completed());
        $tp = ProcStatus::inList(ProcStatus::treatmentPlanned());
        $tpOrCompleted = ProcStatus::inList([...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = $this->baseQuery($request, $clinicRegistry)
            ->selectRaw('pl.ProvNum')
            ->tap(fn (Builder $q) => $this->selectScheduleSplit($q, $tp))
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$completed}) THEN pl.ProcFee ELSE 0 END) as completed_tx")
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpOrCompleted}) THEN pl.ProcFee ELSE 0 END) as total_tx_plan")
            ->selectRaw('COUNT(DISTINCT pl.PatNum) as tx_presented_count')
            ->groupBy('pl.ProvNum');

        $totalRecords = DB::query()->fromSub($query, 'sub')->count();

        // Patients seen by provider (completed procs with ProcDate in range)
        $patientsSeenByProv = $this->scopeQuery($request, $clinicRegistry)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereNotNull('pl.ProcDate')
            ->where('pl.ProcDate', '!=', '0001-01-01')
            ->where('pl.ProcDate', '!=', '0000-00-00')
            ->when($start_date && $end_date, fn ($q) => $q->whereBetween('pl.ProcDate', [$start_date, $end_date]))
            ->selectRaw('pl.ProvNum, COUNT(DISTINCT pl.PatNum) as patients_seen')
            ->groupBy('pl.ProvNum')
            ->pluck('patients_seen', 'ProvNum');

        // Get provider details map
        $provMap = OdProvider::all()->keyBy('ProvNum');

        $records = $query->get();

        $stagedRows = [];
        foreach ($records as $r) {
            $provNum = (int) $r->ProvNum;
            $r->patients_seen = (int) ($patientsSeenByProv[$provNum] ?? 0);
            $provider = $provMap->get($provNum);
            if ($provider) {
                $name = trim(($provider->LName ?? '').(($provider->LName && $provider->PName) ? ', ' : '').($provider->PName ?? ''));
                $provName = $name ?: ($provider->Abbr ?: 'Provider '.$provNum);
            } else {
                $provName = $provNum === 0 ? 'Unassigned' : 'Provider '.$provNum;
            }

            $stagedRows[] = $this->mapRowMetrics($r, $txAcceptance) + [
                'prov_num' => $provNum,
                'provider_name' => $provName,
            ];
        }

        // Sort by total_tx_plan desc by default
        usort($stagedRows, fn ($a, $b) => $b['total_tx_plan_raw'] <=> $a['total_tx_plan_raw']);

        $heat = $this->computeHeatmapTiers($stagedRows);

        $paginated = array_slice($stagedRows, $start, $length > 0 ? $length : count($stagedRows));

        $data = [];
        foreach ($paginated as $row) {
            $data[] = [
                'prov_num' => $row['prov_num'],
                'provider' => $row['provider_name'],
                'total_tx_plan' => $this->formatCurrency($row['total_tx_plan_raw']),
                'tx_scheduled' => $this->formatCurrency($row['tx_scheduled_raw']),
                'tx_unscheduled' => $this->formatCurrency($row['tx_unscheduled_raw']),
                'completed_tx' => $this->formatCurrency($row['completed_tx_raw']),
                'case_acceptance' => $this->formatPercent($row['case_acceptance_raw']),
                'tx_presented' => $row['tx_presented_raw'],
                'avg_tx_plan' => $this->formatCurrency($row['avg_tx_plan_raw']),
                'patients_with_tx' => $this->formatPercent($row['patients_with_tx_raw']),
                'raw' => [
                    'total_tx_plan' => $row['total_tx_plan_raw'],
                    'tx_scheduled' => $row['tx_scheduled_raw'],
                    'tx_unscheduled' => $row['tx_unscheduled_raw'],
                    'completed_tx' => $row['completed_tx_raw'],
                    'case_acceptance' => $row['case_acceptance_raw'],
                    'tx_presented' => $row['tx_presented_raw'],
                    'avg_tx_plan' => $row['avg_tx_plan_raw'],
                    'patients_with_tx' => $row['patients_with_tx_raw'],
                ],
                'heat' => [
                    'total_tx_plan' => $this->getTierClass($heat, 'total_tx_plan', $row['total_tx_plan_raw']),
                    'tx_scheduled' => $this->getTierClass($heat, 'tx_scheduled', $row['tx_scheduled_raw']),
                    'tx_unscheduled' => $this->getTierClass($heat, 'tx_unscheduled', $row['tx_unscheduled_raw']),
                    'completed_tx' => $this->getTierClass($heat, 'completed_tx', $row['completed_tx_raw']),
                    'case_acceptance' => $this->getTierClass($heat, 'case_acceptance', $row['case_acceptance_raw']),
                    'tx_presented' => $this->getTierClass($heat, 'tx_presented', (float) $row['tx_presented_raw']),
                    'avg_tx_plan' => $this->getTierClass($heat, 'avg_tx_plan', $row['avg_tx_plan_raw']),
                    'patients_with_tx' => $this->getTierClass($heat, 'patients_with_tx', $row['patients_with_tx_raw']),
                ],
            ];
        }

        $summary = $this->computeSummaryRows($stagedRows, $txAcceptance);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
            'average' => $summary['average'],
            'total' => $summary['total'],
        ]);
    }

    /**
     * By Location aggregated dataset.
     */
    public function dataLocation(Request $request, TreatmentAcceptanceService $txAcceptance, ClinicRegistry $clinicRegistry): JsonResponse
    {
        $draw = (int) $request->get('draw', 1);
        $start = (int) $request->get('start', 0);
        $length = $request->has('length') ? (int) $request->get('length') : -1;

        $completed = ProcStatus::inList(ProcStatus::completed());
        $tp = ProcStatus::inList(ProcStatus::treatmentPlanned());
        $tpOrCompleted = ProcStatus::inList([...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = $this->baseQuery($request, $clinicRegistry)
            ->selectRaw('pl.office_id, pl.ClinicNum')
            ->tap(fn (Builder $q) => $this->selectScheduleSplit($q, $tp))
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$completed}) THEN pl.ProcFee ELSE 0 END) as completed_tx")
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpOrCompleted}) THEN pl.ProcFee ELSE 0 END) as total_tx_plan")
            ->selectRaw('COUNT(DISTINCT pl.PatNum) as tx_presented_count')
            ->groupBy('pl.office_id', 'pl.ClinicNum');

        $totalRecords = DB::query()->fromSub($query, 'sub')->count();

        // Patients seen by location (completed procs with ProcDate in range)
        $locKeySql = DB::getDriverName() === 'sqlite'
            ? "pl.office_id || ':' || pl.ClinicNum"
            : "CONCAT(pl.office_id, ':', pl.ClinicNum)";

        $patientsSeenByLoc = $this->scopeQuery($request, $clinicRegistry)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereNotNull('pl.ProcDate')
            ->where('pl.ProcDate', '!=', '0001-01-01')
            ->where('pl.ProcDate', '!=', '0000-00-00')
            ->when($start_date && $end_date, fn ($q) => $q->whereBetween('pl.ProcDate', [$start_date, $end_date]))
            ->selectRaw("{$locKeySql} as loc_key, COUNT(DISTINCT pl.PatNum) as patients_seen")
            ->groupBy('pl.office_id', 'pl.ClinicNum')
            ->pluck('patients_seen', 'loc_key');

        $records = $query->get();

        $stagedRows = [];
        foreach ($records as $r) {
            $officeId = (int) $r->office_id;
            $clinicNum = (int) $r->ClinicNum;
            $locKey = "{$officeId}:{$clinicNum}";
            $r->patients_seen = (int) ($patientsSeenByLoc[$locKey] ?? 0);
            $location = $clinicRegistry->locationFor($officeId, $clinicNum);

            $stagedRows[] = $this->mapRowMetrics($r, $txAcceptance) + [
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
                'location_name' => $location->name,
            ];
        }

        // Sort by total_tx_plan desc by default
        usort($stagedRows, fn ($a, $b) => $b['total_tx_plan_raw'] <=> $a['total_tx_plan_raw']);

        $heat = $this->computeHeatmapTiers($stagedRows);

        $paginated = array_slice($stagedRows, $start, $length > 0 ? $length : count($stagedRows));

        $data = [];
        foreach ($paginated as $row) {
            $data[] = [
                'clinic_num' => $row['clinic_num'],
                'location' => $row['location_name'],
                'total_tx_plan' => $this->formatCurrency($row['total_tx_plan_raw']),
                'tx_scheduled' => $this->formatCurrency($row['tx_scheduled_raw']),
                'tx_unscheduled' => $this->formatCurrency($row['tx_unscheduled_raw']),
                'completed_tx' => $this->formatCurrency($row['completed_tx_raw']),
                'case_acceptance' => $this->formatPercent($row['case_acceptance_raw']),
                'tx_presented' => $row['tx_presented_raw'],
                'avg_tx_plan' => $this->formatCurrency($row['avg_tx_plan_raw']),
                'patients_with_tx' => $this->formatPercent($row['patients_with_tx_raw']),
                'raw' => [
                    'total_tx_plan' => $row['total_tx_plan_raw'],
                    'tx_scheduled' => $row['tx_scheduled_raw'],
                    'tx_unscheduled' => $row['tx_unscheduled_raw'],
                    'completed_tx' => $row['completed_tx_raw'],
                    'case_acceptance' => $row['case_acceptance_raw'],
                    'tx_presented' => $row['tx_presented_raw'],
                    'avg_tx_plan' => $row['avg_tx_plan_raw'],
                    'patients_with_tx' => $row['patients_with_tx_raw'],
                ],
                'heat' => [
                    'total_tx_plan' => $this->getTierClass($heat, 'total_tx_plan', $row['total_tx_plan_raw']),
                    'tx_scheduled' => $this->getTierClass($heat, 'tx_scheduled', $row['tx_scheduled_raw']),
                    'tx_unscheduled' => $this->getTierClass($heat, 'tx_unscheduled', $row['tx_unscheduled_raw']),
                    'completed_tx' => $this->getTierClass($heat, 'completed_tx', $row['completed_tx_raw']),
                    'case_acceptance' => $this->getTierClass($heat, 'case_acceptance', $row['case_acceptance_raw']),
                    'tx_presented' => $this->getTierClass($heat, 'tx_presented', (float) $row['tx_presented_raw']),
                    'avg_tx_plan' => $this->getTierClass($heat, 'avg_tx_plan', $row['avg_tx_plan_raw']),
                    'patients_with_tx' => $this->getTierClass($heat, 'patients_with_tx', $row['patients_with_tx_raw']),
                ],
            ];
        }

        $summary = $this->computeSummaryRows($stagedRows, $txAcceptance);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
            'average' => $summary['average'],
            'total' => $summary['total'],
        ]);
    }

    /**
     * CSV Export.
     */
    public function exportCsv(Request $request, TreatmentAcceptanceService $txAcceptance, ClinicRegistry $clinicRegistry): StreamedResponse
    {
        $tab = $request->input('tab', 'provider');
        $fileName = 'tx-miner-'.$tab.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($request, $tab, $txAcceptance, $clinicRegistry) {
            $handle = fopen('php://output', 'w');

            $firstCol = match ($tab) {
                'month' => 'Month',
                'location' => 'Location',
                default => 'Provider',
            };

            fputcsv($handle, [
                $firstCol,
                'Total TX Plan',
                'Tx Scheduled',
                'Tx Unscheduled',
                'Completed Tx',
                'Case Acceptance %',
                '# TX Plan Presented',
                'Average Treatment Plan',
                'Patients with Tx Plan %',
            ]);

            if ($tab === 'provider') {
                $response = $this->dataProvider($request, $txAcceptance, $clinicRegistry);
            } elseif ($tab === 'location') {
                $response = $this->dataLocation($request, $txAcceptance, $clinicRegistry);
            } else {
                $response = $this->data($request, $txAcceptance, $clinicRegistry);
            }

            $jsonData = $response->getData(true);
            $rows = $jsonData['data'] ?? [];

            foreach ($rows as $r) {
                $entity = $r['provider'] ?? ($r['location'] ?? ($r['month'] ?? ''));
                fputcsv($handle, [
                    $entity,
                    $r['total_tx_plan'] ?? '',
                    $r['tx_scheduled'] ?? '',
                    $r['tx_unscheduled'] ?? '',
                    $r['completed_tx'] ?? '',
                    $r['case_acceptance'] ?? '',
                    $r['tx_presented'] ?? '',
                    $r['avg_tx_plan'] ?? '',
                    $r['patients_with_tx'] ?? '',
                ]);
            }

            if (! empty($jsonData['average'])) {
                $avg = $jsonData['average'];
                fputcsv($handle, [
                    'Average:',
                    $avg['total_tx_plan'] ?? '',
                    $avg['tx_scheduled'] ?? '',
                    $avg['tx_unscheduled'] ?? '',
                    $avg['completed_tx'] ?? '',
                    $avg['case_acceptance'] ?? '',
                    $avg['tx_presented'] ?? '',
                    $avg['avg_tx_plan'] ?? '',
                    $avg['patients_with_tx'] ?? '',
                ]);
            }

            if (! empty($jsonData['total'])) {
                $tot = $jsonData['total'];
                fputcsv($handle, [
                    'Total:',
                    $tot['total_tx_plan'] ?? '',
                    $tot['tx_scheduled'] ?? '',
                    $tot['tx_unscheduled'] ?? '',
                    $tot['completed_tx'] ?? '',
                    $tot['case_acceptance'] ?? '',
                    $tot['tx_presented'] ?? '',
                    $tot['avg_tx_plan'] ?? '',
                    $tot['patients_with_tx'] ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * AJAX endpoint for Tx Miner Drill-down modal.
     */
    public function drilldown(Request $request, ClinicRegistry $clinicRegistry, TxMinerPatientBreakdownService $breakdown)
    {
        $metric = $request->input('metric', 'total_tx_plan');

        if ($metric === 'month') {
            return $this->drilldownMonth($request, $clinicRegistry, $breakdown);
        }

        $provNum = $request->input('prov_num');
        $clinicNum = $request->input('clinic_num');
        $officeId = $request->input('office_id');
        $month = $request->input('month');

        $query = TxScheduling::joinScheduledAppointment($this->baseQuery($request, $clinicRegistry));

        // Scope to specific month if requested (e.g. '2026-07' or formatted 'July 2026')
        if ($month) {
            try {
                $monthDate = Carbon::hasFormat($month, 'Y-m')
                    ? Carbon::createFromFormat('!Y-m', $month)
                    : Carbon::parse($month);
                $query->whereBetween('pl.DateTP', [
                    $monthDate->copy()->startOfMonth()->toDateString(),
                    $monthDate->copy()->endOfMonth()->toDateString(),
                ]);
            } catch (\Exception $e) {
                // fallback if unparseable
            }
        }

        if ($provNum) {
            $query->where('pl.ProvNum', (int) $provNum);
        }

        if ($clinicNum !== null && $clinicNum !== '' && $clinicNum !== 'all') {
            $query->where('pl.ClinicNum', (int) $clinicNum);
        }

        if ($officeId) {
            $query->where('pl.office_id', (int) $officeId);
        }

        // Apply metric filter
        $title = 'Treatment Plan Breakdown';
        switch ($metric) {
            case 'tx_scheduled':
                $title = 'Tx Scheduled Breakdown';
                $query->whereIn('pl.ProcStatus', ProcStatus::treatmentPlanned())
                    ->whereRaw(TxScheduling::scheduledSql());
                break;
            case 'tx_unscheduled':
                $title = 'Tx Unscheduled Breakdown';
                $query->whereIn('pl.ProcStatus', ProcStatus::treatmentPlanned())
                    ->whereRaw(TxScheduling::unscheduledSql());
                break;
            case 'completed_tx':
                $title = 'Completed Tx Breakdown';
                $query->whereIn('pl.ProcStatus', ProcStatus::completed());
                break;
            case 'total_tx_plan':
            case 'tx_presented':
            default:
                $title = 'Total Tx Plan Breakdown';
                $query->whereIn('pl.ProcStatus', [...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()]);
                break;
        }

        if ($month) {
            try {
                $monthDate = Carbon::hasFormat($month, 'Y-m')
                    ? Carbon::createFromFormat('!Y-m', $month)
                    : Carbon::parse($month);
                $title .= ' — '.$monthDate->format('F Y');
            } catch (\Exception $e) {
                $title .= ' — '.$month;
            }
        }

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

        // Select required columns and join procedure codes matching office_id
        $query->leftJoin('od_procedures as pc_drill', function ($join) {
            $join->on('pl.CodeNum', '=', 'pc_drill.CodeNum')
                ->on('pl.office_id', '=', 'pc_drill.office_id');
        })
            ->select([
                'pl.office_id',
                'pl.PatNum',
                'pl.ProvNum',
                'pl.ClinicNum',
                'pl.ProcDate',
                'pl.DateTP',
                'pl.ProcFee',
                'pl.Surf',
                'pl.ToothNum',
                'pl.ProcStatus',
                TxScheduling::scheduledAptColumn(),
                'pc_drill.ProcCode',
                'pc_drill.Descript as proc_descript',
            ])
            ->orderBy('pl.DateTP', 'desc')
            ->limit(500);

        $logs = $query->get();

        $patNums = $logs->pluck('PatNum')->filter()->unique();
        $patMap = ! $patNums->isEmpty()
            ? OdPatient::whereIn('PatNum', $patNums)->get()->keyBy('PatNum')
            : collect();

        $provMap = OdProvider::all()->keyBy('ProvNum');

        $columns = [
            ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
            ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'descript', 'label' => 'Description', 'type' => 'text'],
            ['key' => 'tooth_surf', 'label' => 'Tooth / Surf', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Fee', 'type' => 'money', 'agg' => 'sum'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
        ];

        if (! $provNum) {
            $columns[] = ['key' => 'provider', 'label' => 'Provider', 'type' => 'text'];
        }
        if (! $clinicNum || $clinicNum === 'all') {
            $columns[] = ['key' => 'location', 'label' => 'Location', 'type' => 'text'];
        }

        $rows = [];
        $totalFee = 0.0;

        foreach ($logs as $log) {
            $fee = (float) ($log->ProcFee ?? 0);
            $totalFee += $fee;

            $patient = $patMap->get($log->PatNum);
            $patName = $patient
                ? trim(($patient->LName ?? '').(($patient->LName && $patient->FName) ? ', ' : '').($patient->FName ?? ''))
                : 'Patient '.$log->PatNum;

            $provider = $provMap->get($log->ProvNum);
            $provName = $provider
                ? trim(($provider->LName ?? '').(($provider->LName && $provider->PName) ? ', ' : '').($provider->PName ?? ''))
                : ($log->ProvNum ? 'Provider '.$log->ProvNum : 'Unassigned');

            $isCompleted = in_array((string) $log->ProcStatus, ProcStatus::completed(), true);
            $statusText = $isCompleted
                ? 'Completed'
                : (TxScheduling::isScheduled($log) ? 'Scheduled' : 'Unscheduled');

            $toothSurf = trim(($log->ToothNum ?? '').($log->Surf ? ' / '.$log->Surf : ''));

            $displayDate = (! empty($log->DateTP) && $log->DateTP !== '0001-01-01' && $log->DateTP !== '0000-00-00')
                ? Carbon::parse($log->DateTP)->format('M d, Y')
                : ($log->ProcDate ? Carbon::parse($log->ProcDate)->format('M d, Y') : '—');

            $r = [
                'pat_id' => $log->PatNum,
                'patient' => [
                    'label' => $patName,
                    'link' => true,
                ],
                'date' => $displayDate,
                'code' => $log->ProcCode ?? '—',
                'descript' => $log->proc_descript ?? '—',
                'tooth_surf' => $toothSurf ?: '—',
                'amount' => $fee,
                'status' => $statusText,
            ];

            if (! $provNum) {
                $r['prov_id'] = $log->ProvNum;
                $r['provider'] = [
                    'label' => $provName,
                    'link' => true,
                ];
            }

            if (! $clinicNum || $clinicNum === 'all') {
                $r['location'] = $clinicRegistry->name((int) $log->ClinicNum, (int) $log->office_id);
            }

            $rows[] = $r;
        }

        $totals = ['amount' => $totalFee];

        return view('components.app-components.drilldown.table-content', compact('title', 'columns', 'rows', 'totals', 'providerInfo'));
    }

    /**
     * Patient-level Treatment Miner Drill-down for Month.
     */
    protected function drilldownMonth(Request $request, ClinicRegistry $clinicRegistry, TxMinerPatientBreakdownService $breakdown)
    {
        [$startDate, $endDate] = $this->drilldownPeriod($request);

        $scope = $this->scopeQuery($request, $clinicRegistry);

        $provNum = $request->input('prov_num');
        $clinicNum = $request->input('clinic_num');
        $officeId = $request->input('office_id');
        if ($provNum) {
            $scope->where('pl.ProvNum', (int) $provNum);
        }
        if ($clinicNum !== null && $clinicNum !== '' && $clinicNum !== 'all') {
            $scope->where('pl.ClinicNum', (int) $clinicNum);
        }
        if ($officeId && $officeId !== 'all') {
            $scope->where('pl.office_id', (int) $officeId);
        }

        $patients = $breakdown->patients($scope, $startDate, $endDate, now());

        $rows = [];
        foreach ($patients as $p) {
            $provNames = array_column($p->providers, 'name');
            sort($provNames, SORT_STRING | SORT_FLAG_CASE);
            $provAbbrs = array_filter(array_column($p->providers, 'abbr'));
            sort($provAbbrs, SORT_STRING | SORT_FLAG_CASE);
            $provIds = implode(',', array_column($p->providers, 'num'));

            $rows[] = [
                'pat_id' => $p->patNum,
                'patient' => [
                    'label' => $p->name,
                    'link' => true,
                ],
                'chart_num' => $p->chartNumber,
                'phone' => $p->homePhone,
                'mobile' => $p->wirelessPhone,
                'email' => $p->email,
                'type' => $p->isNew ? 'New' : 'Existing',
                'tx_scheduled' => $p->txScheduled,
                'tx_unscheduled' => $p->txUnscheduled,
                'completed_tx' => $p->completedTx,
                'next_visit' => $p->nextVisit ?? '',
                'next_hygiene_visit' => $p->nextHygieneVisit ?? '',
                'referred_to' => '',
                'referral_source' => '',
                'remaining_benefits' => '—',
                'status' => $p->status,
                'provider' => implode('|', $provNames),
                'provider_id' => $provAbbrs ? $provIds.' - '.implode(',', $provAbbrs) : $provIds,
                'insurance' => $p->insurance ?? '',
                'date_planned' => implode(',', $p->datesPlanned),
                'date_created' => implode(',', $p->datesCreated),
            ];
        }

        $columns = [
            ['key' => 'patient', 'label' => 'Patient', 'type' => 'text'],
            ['key' => 'pat_id', 'label' => 'Patient ID', 'type' => 'text'],
            ['key' => 'chart_num', 'label' => 'Chart #', 'type' => 'text'],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['key' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
            ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'tx_scheduled', 'label' => 'Tx Scheduled', 'type' => 'money', 'agg' => 'sum'],
            ['key' => 'tx_unscheduled', 'label' => 'Tx Unscheduled', 'type' => 'money', 'agg' => 'sum'],
            ['key' => 'completed_tx', 'label' => 'Completed TX $', 'type' => 'money', 'agg' => 'sum'],
            ['key' => 'next_visit', 'label' => 'Next Visit Date', 'type' => 'text'],
            ['key' => 'next_hygiene_visit', 'label' => 'Next Hygiene Visit Date', 'type' => 'text'],
            ['key' => 'referred_to', 'label' => 'Referred To', 'type' => 'text'],
            ['key' => 'referral_source', 'label' => 'Referral Source', 'type' => 'text'],
            // Not computable yet: benefit / patplan / inssub are not synced.
            ['key' => 'remaining_benefits', 'label' => 'Remaining Benefits', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'provider', 'label' => 'Preferred Provider', 'type' => 'text'],
            ['key' => 'provider_id', 'label' => 'Provider ID', 'type' => 'text'],
            ['key' => 'insurance', 'label' => 'Insurance', 'type' => 'text'],
            ['key' => 'date_planned', 'label' => 'Date Planned', 'type' => 'text'],
            ['key' => 'date_created', 'label' => 'Date Created', 'type' => 'text'],
        ];

        $totals = [
            'tx_scheduled' => array_sum(array_column($rows, 'tx_scheduled')),
            'tx_unscheduled' => array_sum(array_column($rows, 'tx_unscheduled')),
            'completed_tx' => array_sum(array_column($rows, 'completed_tx')),
        ];

        $title = 'Treatment Miner Breakdown — '.Carbon::parse($startDate)->format('M Y');
        $providerInfo = null;

        return view('components.app-components.drilldown.table-content', compact('title', 'columns', 'rows', 'totals', 'providerInfo'));
    }

    /**
     * The month a month drill-down covers ('Y-m' or any parseable date), falling back to
     * start_date/end_date and then the current month.
     *
     * @return array{0: string, 1: string} ['Y-m-d', 'Y-m-d']
     */
    private function drilldownPeriod(Request $request): array
    {
        $month = $request->input('month');
        if ($month) {
            try {
                $monthDate = Carbon::hasFormat($month, 'Y-m')
                    ? Carbon::createFromFormat('!Y-m', $month)
                    : Carbon::parse($month);

                return [$monthDate->copy()->startOfMonth()->toDateString(), $monthDate->copy()->endOfMonth()->toDateString()];
            } catch (\Exception $e) {
                // Unparseable month: fall through to the explicit range.
            }
        }

        return [
            $request->input('start_date', now()->startOfMonth()->toDateString()),
            $request->input('end_date', now()->endOfMonth()->toDateString()),
        ];
    }

    /**
     * Shared Base Query with comprehensive multi-parameter filtering.
     */
    /**
     * Add the tx_scheduled / tx_unscheduled SUMs (rule lives in TxScheduling).
     *
     * @param  string  $tpList  quoted treatment-planned status list for SQL IN (...)
     */
    private function selectScheduleSplit(Builder $query, string $tpList): void
    {
        $scheduled = TxScheduling::scheduledSql();
        $unscheduled = TxScheduling::unscheduledSql();

        TxScheduling::joinScheduledAppointment($query)
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpList}) AND {$scheduled} THEN pl.ProcFee ELSE 0 END) as tx_scheduled")
            ->selectRaw("SUM(CASE WHEN pl.ProcStatus IN ({$tpList}) AND {$unscheduled} THEN pl.ProcFee ELSE 0 END) as tx_unscheduled");
    }

    protected function baseQuery(Request $request, ClinicRegistry $clinicRegistry, ?string $overrideStartDate = null, ?string $overrideEndDate = null): Builder
    {
        $validStatuses = [...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()];
        $yearSql = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', pl.DateTP) AS INT)"
            : 'YEAR(pl.DateTP)';

        $query = $this->scopeQuery($request, $clinicRegistry)
            ->whereNotNull('pl.DateTP')
            ->where('pl.DateTP', '!=', '0001-01-01')
            ->where('pl.DateTP', '!=', '0000-00-00')
            ->whereIn('pl.ProcStatus', $validStatuses)
            ->whereRaw("{$yearSql} >= 2000");

        // Date Range filter
        $start = $overrideStartDate ?? $request->input('start_date');
        $end = $overrideEndDate ?? $request->input('end_date');
        if ($start && $end) {
            $query->whereBetween('pl.DateTP', [$start, $end]);
        }

        return $query;
    }

    /**
     * Procedure logs narrowed by every Tx Miner filter except the date range
     * (locations, providers, procedures, patients, line of business).
     */
    protected function scopeQuery(Request $request, ClinicRegistry $clinicRegistry): Builder
    {
        $query = DB::table('od_procedure_logs as pl');

        // Scopes via LocationSelection
        $locationSelection = $this->resolveLocations($request, $clinicRegistry);
        $scopes = $locationSelection->scopes();

        // Support legacy clinic / clinics query params when locations param is not provided
        $clinic = $request->input('clinic') ?? $request->input('clinic_num');
        $clinics = $request->input('clinics');
        if ($clinic !== null && $clinic !== '' && $clinic !== 'all' && ! $request->filled('locations')) {
            $scopes = [Office::getActiveOfficeId() => [(int) $clinic]];
        } elseif ($clinics && ! $request->filled('locations')) {
            $clinicsArray = is_array($clinics) ? $clinics : explode(',', (string) $clinics);
            $clinicsArray = array_filter(array_map('trim', $clinicsArray));
            if (! empty($clinicsArray)) {
                $scopes = [Office::getActiveOfficeId() => array_map('intval', $clinicsArray)];
            }
        }

        $query->where(function ($q) use ($scopes) {
            foreach ($scopes as $officeId => $clinicNums) {
                $q->orWhere(function ($sub) use ($officeId, $clinicNums) {
                    $sub->where('pl.office_id', $officeId);
                    if (! empty($clinicNums)) {
                        $sub->whereIn('pl.ClinicNum', $clinicNums);
                    }
                });
            }
        });

        // Provider filter
        $providers = $request->input('providers') ?? $request->input('prov_nums');
        if ($providers) {
            $provArray = is_array($providers) ? $providers : explode(',', (string) $providers);
            $provArray = array_filter(array_map('trim', $provArray));
            if (! empty($provArray)) {
                $query->whereIn('pl.ProvNum', array_map('intval', $provArray));
            }
        }

        // Procedure filter
        $procedures = $request->input('procedures') ?? $request->input('code_nums');
        if ($procedures) {
            $procArray = is_array($procedures) ? $procedures : explode(',', (string) $procedures);
            $procArray = array_filter(array_map('trim', $procArray));
            if (! empty($procArray)) {
                $query->whereIn('pl.CodeNum', array_map('intval', $procArray));
            }
        }

        // Patient filter
        $patients = $request->input('patients') ?? $request->input('pat_nums');
        if ($patients) {
            $patArray = is_array($patients) ? $patients : explode(',', (string) $patients);
            $patArray = array_filter(array_map('trim', $patArray));
            if (! empty($patArray)) {
                $query->whereIn('pl.PatNum', array_map('intval', $patArray));
            }
        }

        // Line of Business filter
        $lobs = $request->input('lobs') ?? $request->input('lob');
        if ($lobs) {
            $lobArray = is_array($lobs) ? $lobs : explode(',', (string) $lobs);
            $lobArray = array_filter(array_map('trim', $lobArray));
            if (! empty($lobArray)) {
                $query->join('od_procedures as pc_lob', function ($join) {
                    $join->on('pl.CodeNum', '=', 'pc_lob.CodeNum')
                        ->on('pl.office_id', '=', 'pc_lob.office_id');
                });
                $query->where(function ($q) use ($lobArray) {
                    foreach ($lobArray as $lob) {
                        switch (strtolower($lob)) {
                            case 'hygiene':
                                $q->orWhere('pc_lob.IsHygiene', 'true');
                                break;
                            case 'doctor':
                                $q->orWhere('pc_lob.IsHygiene', 'false');
                                break;
                            case 'endo':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D3%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', '3%');
                                break;
                            case 'perio':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D4%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', '4%');
                                break;
                            case 'oral surgery':
                            case 'os':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D7%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', '7%');
                                break;
                            case 'ortho':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D8%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', '8%');
                                break;
                            case 'invisalign':
                                $q->orWhereIn('pc_lob.ProcCode', ['D8090', 'D8080', 'D8070', 'D8040']);
                                break;
                            case 'pedo':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D1%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', '1%');
                                break;
                            case 'prostho':
                                $q->orWhere('pc_lob.ProcCode', 'LIKE', 'D5%')
                                    ->orWhere('pc_lob.ProcCode', 'LIKE', 'D6%')
                                    ->orWhere('pc_lob.IsProsth', 'true');
                                break;
                            case 'others':
                                $q->orWhere(function ($sub) {
                                    $sub->where('pc_lob.ProcCode', 'NOT LIKE', 'D1%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D3%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D4%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D5%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D6%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D7%')
                                        ->where('pc_lob.ProcCode', 'NOT LIKE', 'D8%')
                                        ->whereNotNull('pc_lob.ProcCode')
                                        ->where('pc_lob.ProcCode', '!=', '');
                                });
                                break;
                            case 'not set':
                                $q->orWhereNull('pc_lob.ProcCode')
                                    ->orWhere('pc_lob.ProcCode', '');
                                break;
                            default:
                                break;
                        }
                    }
                });
            }
        }

        return $query;
    }

    private function resolveLocations(Request $request, ClinicRegistry $clinicRegistry): LocationSelection
    {
        if ($request->filled('locations')) {
            return $clinicRegistry->select($request->input('locations'));
        }

        if ($request->filled('office_id') || $request->filled('clinic_num') || $request->filled('clinic_id')) {
            $officeInput = $request->input('office_id');
            if ($officeInput === 'all') {
                return $clinicRegistry->select('all');
            }
            $officeId = ($officeInput !== null && $officeInput !== '') ? (int) $officeInput : Office::getActiveOfficeId();
            $clinicInput = $request->input('clinic_id') ?? $request->input('clinic_num');
            if ($clinicInput === null && $officeId !== null) {
                $clinicInput = $clinicRegistry->getActiveClinicNum($officeId);
            }
            if ($clinicInput !== null && $clinicInput !== '' && $clinicInput !== 'all') {
                return $clinicRegistry->select("{$officeId}:{$clinicInput}");
            }
            if ($officeId !== null) {
                return $clinicRegistry->select((string) $officeId);
            }
        }

        return $clinicRegistry->select(null);
    }

    /**
     * Map raw row query to standard metrics.
     */
    protected function mapRowMetrics($r, TreatmentAcceptanceService $txAcceptance): array
    {
        $txScheduled = (float) ($r->tx_scheduled ?? 0);
        $unscheduled = (float) ($r->tx_unscheduled ?? 0);
        $completed = (float) ($r->completed_tx ?? 0);
        $totalTx = (float) ($r->total_tx_plan ?? ($txScheduled + $unscheduled + $completed));

        $caseAcceptance = $totalTx > 0
            ? min(100.0, round((($completed + $txScheduled) / $totalTx) * 100, 2))
            : 0.0;

        $txPresentedCount = (int) ($r->tx_presented_count ?? 0);
        $avgTxPlan = $txPresentedCount > 0 ? round($totalTx / $txPresentedCount, 2) : 0.0;

        $patientsSeen = (int) ($r->patients_seen ?? 0);
        $patientsTxPct = $patientsSeen > 0
            ? round(($txPresentedCount / $patientsSeen) * 100, 2)
            : 0.0;

        return [
            'total_tx_plan_raw' => $totalTx,
            'tx_scheduled_raw' => $txScheduled,
            'tx_unscheduled_raw' => $unscheduled,
            'completed_tx' => $completed,
            'completed_tx_raw' => $completed,
            'case_acceptance_raw' => $caseAcceptance,
            'tx_presented_raw' => $txPresentedCount,
            'avg_tx_plan_raw' => $avgTxPlan,
            'patients_with_tx_raw' => $patientsTxPct,
            'patients_seen' => $patientsSeen,
            'patients_with_tp' => $txPresentedCount,
        ];
    }

    protected function formatCurrency(float $val): string
    {
        if ($val == 0) {
            return '$ 0';
        }

        return '$ '.number_format($val, 2);
    }

    protected function formatPercent(float $val): string
    {
        return number_format($val, 2).'%';
    }

    /**
     * Compute 20th and 80th percentile heatmap tiers across dataset.
     */
    protected function computeHeatmapTiers(array $stagedRows): array
    {
        $cols = [
            'total_tx_plan' => false,
            'tx_scheduled' => false,
            'tx_unscheduled' => true, // Inverted: lower unscheduled is top (green)
            'completed_tx' => false,
            'case_acceptance' => false,
            'tx_presented' => false,
            'avg_tx_plan' => false,
            'patients_with_tx' => false,
        ];

        $heat = [];
        $n = count($stagedRows);
        if ($n < 2) {
            return [];
        }

        foreach ($cols as $col => $invert) {
            $vals = array_column($stagedRows, $col.'_raw');
            sort($vals);
            $p20 = $vals[(int) floor(0.2 * ($n - 1))];
            $p80 = $vals[(int) ceil(0.8 * ($n - 1))];
            $heat[$col] = [
                'p20' => $p20,
                'p80' => $p80,
                'invert' => $invert,
            ];
        }

        return $heat;
    }

    protected function getTierClass(?array $heat, string $col, float $val): string
    {
        if (! $heat || ! isset($heat[$col])) {
            return 'mid';
        }
        $h = $heat[$col];
        $isTop = $val >= $h['p80'];
        $isBottom = $val <= $h['p20'];
        if ($h['invert']) {
            if ($isBottom) {
                return 'top';
            }
            if ($isTop) {
                return 'bottom';
            }

            return 'mid';
        }
        if ($isTop) {
            return 'top';
        }
        if ($isBottom) {
            return 'bottom';
        }

        return 'mid';
    }

    /**
     * Summary footer rows: Average: and Total:.
     */
    protected function computeSummaryRows(array $stagedRows, TreatmentAcceptanceService $txAcceptance): array
    {
        $count = count($stagedRows);
        if ($count === 0) {
            return [
                'average' => [
                    'total_tx_plan' => '$ 0',
                    'tx_scheduled' => '$ 0',
                    'tx_unscheduled' => '$ 0',
                    'completed_tx' => '$ 0',
                    'case_acceptance' => '0.00%',
                    'tx_presented' => 0,
                    'avg_tx_plan' => '$ 0',
                    'patients_with_tx' => '0.00%',
                ],
                'total' => [
                    'total_tx_plan' => '$ 0',
                    'tx_scheduled' => '$ 0',
                    'tx_unscheduled' => '$ 0',
                    'completed_tx' => '$ 0',
                    'case_acceptance' => '0.00%',
                    'tx_presented' => 0,
                    'avg_tx_plan' => '$ 0',
                    'patients_with_tx' => '0.00%',
                ],
            ];
        }

        $sumTotalTx = array_sum(array_column($stagedRows, 'total_tx_plan_raw'));
        $sumScheduled = array_sum(array_column($stagedRows, 'tx_scheduled_raw'));
        $sumUnscheduled = array_sum(array_column($stagedRows, 'tx_unscheduled_raw'));
        $sumCompleted = array_sum(array_column($stagedRows, 'completed_tx_raw'));
        $sumPresented = array_sum(array_column($stagedRows, 'tx_presented_raw'));
        $sumPatientsSeen = array_sum(array_column($stagedRows, 'patients_seen'));
        $sumPatientsWithTp = array_sum(array_column($stagedRows, 'patients_with_tp'));

        $overallCaseAcceptance = $sumTotalTx > 0
            ? min(100.0, round((($sumCompleted + $sumScheduled) / $sumTotalTx) * 100, 2))
            : 0.0;
        $overallAvgTxPlan = $sumPresented > 0 ? round($sumTotalTx / $sumPresented, 2) : 0.0;
        $overallPatientsWithTx = $sumPatientsSeen > 0 ? round(($sumPresented / $sumPatientsSeen) * 100, 2) : 0.0;

        $avgTotalTx = $sumTotalTx / $count;
        $avgScheduled = $sumScheduled / $count;
        $avgUnscheduled = $sumUnscheduled / $count;
        $avgCompleted = $sumCompleted / $count;
        $avgPresented = (int) round($sumPresented / $count);
        $avgTxPlan = $count > 0 ? array_sum(array_column($stagedRows, 'avg_tx_plan_raw')) / $count : 0.0;
        $avgPatientsWithTx = $count > 0 ? array_sum(array_column($stagedRows, 'patients_with_tx_raw')) / $count : 0.0;
        $avgCaseAcceptance = $count > 0 ? array_sum(array_column($stagedRows, 'case_acceptance_raw')) / $count : 0.0;

        return [
            'average' => [
                'total_tx_plan' => $this->formatCurrency($avgTotalTx),
                'tx_scheduled' => $this->formatCurrency($avgScheduled),
                'tx_unscheduled' => $this->formatCurrency($avgUnscheduled),
                'completed_tx' => $this->formatCurrency($avgCompleted),
                'case_acceptance' => $this->formatPercent($avgCaseAcceptance),
                'tx_presented' => $avgPresented,
                'avg_tx_plan' => $this->formatCurrency($avgTxPlan),
                'patients_with_tx' => $this->formatPercent($avgPatientsWithTx),
            ],
            'total' => [
                'total_tx_plan' => $this->formatCurrency($sumTotalTx),
                'tx_scheduled' => $this->formatCurrency($sumScheduled),
                'tx_unscheduled' => $this->formatCurrency($sumUnscheduled),
                'completed_tx' => $this->formatCurrency($sumCompleted),
                'case_acceptance' => $this->formatPercent($overallCaseAcceptance),
                'tx_presented' => $sumPresented,
                'avg_tx_plan' => $this->formatCurrency($overallAvgTxPlan),
                'patients_with_tx' => $this->formatPercent($overallPatientsWithTx),
            ],
        ];
    }
}
