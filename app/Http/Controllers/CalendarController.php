<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Models\OdAdjustment;
use App\Models\OdAppointment;
use App\Models\OdProcedureLog;
use App\Services\OpenDental\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CalendarController extends Controller
{
    public function __construct(
        private readonly ClinicRegistry $clinics,
        private readonly ProductionService $production,
        private readonly PatientService $patients,
    ) {}

    public function index()
    {
        return view('calendar.index');
    }

    public function getData(Request $request, CalendarService $calendar)
    {
        $start = $request->get('start') ?? date('Y-m-d');
        $end = $request->get('end') ?? date('Y-m-d');

        Log::info("Fetching appointments from OpenDental API for date range: {$start} to {$end}");

        return response()->json($calendar->events($start, $end));
    }

    public function getResources(Request $request, CalendarService $calendar)
    {
        $date = $request->get('date') ?? date('Y-m-d');
        $activeOnly = $request->get('active_only') == '1';

        return response()->json($calendar->resources($date, $date, $activeOnly));
    }

    /**
     * Daily production figures for the calendar's stats bar.
     *
     *  production            — $ actually PRODUCED that day: completed
     *                          procedures (ProcStatus 'C'/'2') dated that day.
     *                          This is the standard daily-production metric and
     *                          is independent of appointment linkage.
     *  scheduled_production  — $ SCHEDULED that day: the booked fee value of
     *                          appointments in Scheduled status for the day.
     *
     * ProcFee is stored as text, so it is CAST for summing. Date columns may be
     * raw ISO strings (with a 'T') or normalized dates/datetimes, so both are
     * matched via DATE(REPLACE(...)) which is correct either way.
     */
    public function stats(Request $request)
    {
        $date = $request->get('date') ?? date('Y-m-d');

        $gross = (float) OdProcedureLog::query()
            ->whereIn('ProcStatus', ['C', '2'])
            ->whereRaw("DATE(REPLACE(ProcDate, 'T', ' ')) = ?", [$date])
            ->selectRaw('COALESCE(SUM(CAST(ProcFee AS DECIMAL(12,2))), 0) AS total')
            ->value('total');

        $adjustments = (float) OdAdjustment::query()
            ->whereRaw("DATE(REPLACE(AdjDate, 'T', ' ')) = ?", [$date])
            ->selectRaw('COALESCE(SUM(CAST(AdjAmt AS DECIMAL(12,2))), 0) AS total')
            ->value('total');

        $writeoffs = (float) DB::table('od_claim_procs as c')
            ->whereRaw("DATE(REPLACE(c.ProcDate, 'T', ' ')) = ?", [$date])
            ->selectRaw('COALESCE(SUM(CAST(c.WriteOff AS DECIMAL(12,2))), 0) AS total')
            ->value('total');

        $produced = $this->production->netFrom($gross, $adjustments, $writeoffs);

        $scheduled = $gross;

        // Fetch active providers today from appointments on this date
        $providerApts = OdAppointment::query()
            ->whereIn('AptStatus', [1, 2, 4, 5])
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) = ?", [$date])
            ->with('provider')
            ->get();

        $providersData = [];
        $grouped = $providerApts->groupBy(function ($apt) {
            return $apt->ProvNum ? (int) $apt->ProvNum : 0;
        });

        foreach ($grouped as $provNum => $apts) {
            if ($provNum > 0) {
                $firstApt = $apts->first();
                $prov = $firstApt?->provider;
                if ($prov) {
                    $lastName = $prov->LName ?? '';
                    $firstName = $prov->PName ?? '';
                    $initials = (strlen($lastName) >= 2) ? substr($lastName, 0, 2) : substr($lastName, 0, 1);
                    $specialtyText = ($provNum == 81) ? 'Invis' : (($provNum == 64) ? 'Gen' : 'General');
                    $color = '#94a3b8';
                    if ($provNum == 81) {
                        $color = '#6DE5C1';
                    } elseif ($provNum == 64) {
                        $color = '#996BE5';
                    }

                    $providersData[] = [
                        'id' => $provNum,
                        'name' => trim($lastName.', '.$firstName),
                        'initials' => $initials,
                        'specialty' => $specialtyText,
                        'count' => $apts->count(),
                        'color' => $color,
                    ];
                }
            } else {
                $providersData[] = [
                    'id' => 0,
                    'name' => 'Unassigned',
                    'initials' => 'Un',
                    'specialty' => '',
                    'count' => $apts->count(),
                    'color' => '#94a3b8',
                ];
            }
        }

        usort($providersData, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return response()->json([
            'production' => $produced,
            'scheduled_production' => $scheduled,
            'providers' => $providersData,
        ]);
    }

    public function appointmentsDetailsData(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-d');
        $end = $request->get('end') ?? date('Y-m-d');

        // Match by calendar date regardless of whether AptDateTime is a raw ISO
        // 'T' string or a normalized DATETIME (see AppointmentRepository).
        $query = OdAppointment::with(['patient', 'provider'])
            ->select('od_appointments.*')
            ->withSum('procedureLogs as production_total', 'ProcFee')
            ->whereIn('AptStatus', [1, 2, 4, 5])
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end]);

        return DataTables::of($query)
            ->orderColumn('location', 'od_appointments.ClinicNum $1')
            ->orderColumn('patient_name', function ($query, $order) {
                $query->leftJoin('od_patients as p_name', 'od_appointments.PatNum', '=', 'p_name.PatNum')
                    ->orderBy('p_name.LName', $order)
                    ->orderBy('p_name.FName', $order);
            })
            ->orderColumn('appointment_date', 'od_appointments.AptDateTime $1')
            ->orderColumn('appointment_time', 'od_appointments.AptDateTime $1')
            ->orderColumn('appointment_duration', 'LENGTH(od_appointments.Pattern) $1')
            ->orderColumn('operatory_name', 'CAST(od_appointments.Op AS UNSIGNED) $1')
            ->orderColumn('appointment_status', 'od_appointments.AptStatus $1')
            ->orderColumn('patient_age', function ($query, $order) {
                $query->leftJoin('od_patients as p_age', 'od_appointments.PatNum', '=', 'p_age.PatNum')
                    ->orderBy('p_age.Birthdate', $order === 'asc' ? 'desc' : 'asc');
            })
            ->orderColumn('patient_phone', function ($query, $order) {
                $query->leftJoin('od_patients as p_phone', 'od_appointments.PatNum', '=', 'p_phone.PatNum')
                    ->orderBy('p_phone.WirelessPhone', $order);
            })
            ->orderColumn('email_address', function ($query, $order) {
                $query->leftJoin('od_patients as p_email', 'od_appointments.PatNum', '=', 'p_email.PatNum')
                    ->orderBy('p_email.Email', $order);
            })
            ->orderColumn('patient_type', 'od_appointments.IsNewPatient $1')
            ->orderColumn('appointment_notes', 'od_appointments.Note $1')
            ->orderColumn('confirmation_status', 'od_appointments.Confirmed $1')
            ->orderColumn('provider_name', function ($query, $order) {
                $query->leftJoin('od_providers as prov_sort', 'od_appointments.ProvNum', '=', 'prov_sort.ProvNum')
                    ->orderBy('prov_sort.Abbr', $order);
            })
            ->orderColumn('procedure_codes', 'od_appointments.ProcDescript $1')
            ->orderColumn('production', 'production_total $1')
            ->orderColumn('primary_insurance', 'od_appointments.InsPlan1 $1')
            ->orderColumn('secondary_insurance', 'od_appointments.InsPlan2 $1')
            ->orderColumn('referral_source', 'od_appointments.AptNum $1')
            ->orderColumn('unscheduled_tx', 'od_appointments.AptNum $1')
            ->orderColumn('last_visit_date', 'od_appointments.AptNum $1')
            ->addColumn('location', fn ($row) => $this->clinics->name((int) ($row->ClinicNum ?? 0)))
            ->addColumn('patient_name', fn ($row) => trim(($row->patient?->FName ?? '').' '.($row->patient?->LName ?? '')))
            ->addColumn('appointment_date', fn ($row) => (new Carbon($row->AptDateTime))->format('M d, Y'))
            ->addColumn('appointment_time', fn ($row) => (new Carbon($row->AptDateTime))->format('h:i A'))
            ->addColumn('appointment_duration', function ($row) {
                $pattern = $row->Pattern ?? '';
                $minutes = strlen($pattern) > 0 ? strlen($pattern) * 5 : 60;

                return "{$minutes}.00";
            })
            ->addColumn('operatory_name', fn ($row) => 'DR-'.($row->Op ?? ''))
            ->addColumn('appointment_status', function ($row) {
                $map = [
                    1 => 'Scheduled',
                    2 => 'Completed',
                    4 => 'ASAP',
                    5 => 'Broken',
                ];

                return $map[$row->AptStatus] ?? 'Scheduled';
            })
            ->addColumn('patient_age', function ($row) {
                if (! empty($row->patient?->Birthdate) && $row->patient?->Birthdate != '0001-01-01' && $row->patient?->Birthdate != '0000-00-00') {
                    return Carbon::parse($row->patient?->Birthdate)->age;
                }

                return '--';
            })
            ->addColumn('patient_phone', fn ($row) => $row->patient?->WirelessPhone ?: ($row->patient?->HmPhone ?: '--'))
            ->addColumn('email_address', fn ($row) => $row->patient?->Email ?: '--')
            ->addColumn('patient_type', fn ($row) => $row->IsNewPatient ? 'New Patient' : 'Existing')
            ->addColumn('appointment_notes', fn ($row) => $row->Note ?: '--')
            ->addColumn('confirmation_status', fn ($row) => $row->Confirmed ? 'Confirmed' : 'Unconfirmed')
            ->addColumn('provider_name', fn ($row) => $row->provider?->Abbr ?? 'Unknown')
            ->addColumn('procedure_codes', fn ($row) => $row->ProcDescript ?: '--')
            ->addColumn('production', function ($row) {
                return '$ '.number_format($row->production_total ?? 0, 2);
            })
            ->addColumn('primary_insurance', fn ($row) => 'N/A')
            ->addColumn('secondary_insurance', fn ($row) => 'N/A')
            ->addColumn('referral_source', fn ($row) => 'Unknown')
            ->addColumn('unscheduled_tx', fn ($row) => '$ 0.00')
            ->addColumn('last_visit_date', fn ($row) => 'N/A')
            ->make(true);
    }

    public function appointmentCapacityData(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-d');
        $end = $request->get('end') ?? date('Y-m-d');

        // We fetch scheduled & completed appointments for the date frame. Match by calendar
        // date regardless of AptDateTime storage format (see AppointmentRepository).
        $appointments = OdAppointment::whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->whereIn('AptStatus', [1, 2]) // Scheduled & Complete
            ->get();

        // Calculate aggregate metrics
        $scheduledApts = $appointments->count();
        $providerCount = $appointments->pluck('ProvNum')->filter(fn ($p) => (int) $p > 0)->unique()->count();

        $bookedMinutes = $appointments->sum(function ($apt) {
            return strlen($apt->Pattern ?? '') > 0 ? strlen($apt->Pattern) * 5 : 60;
        });

        // Compute Lead Time logic
        $allLeadTimes = [];
        $newPatLeadTimes = [];
        $emergLeadTimes = [];

        foreach ($appointments as $apt) {
            $createdStr = ($apt->SecDateTEntry && $apt->SecDateTEntry !== '0001-01-01T00:00:00')
                ? $apt->SecDateTEntry
                : ($apt->DateTStamp ?? $apt->AptDateTime);

            $createdDt = new Carbon($createdStr);
            $aptDt = new Carbon($apt->AptDateTime);
            $diffDays = max(0, $createdDt->diffInDays($aptDt));

            $allLeadTimes[] = $diffDays;

            if ((bool) $apt->IsNewPatient) {
                $newPatLeadTimes[] = $diffDays;
            }

            $isEmerg = str_contains(strtolower($apt->ProcDescript ?? ''), 'emergency')
                || str_contains(strtolower($apt->ProcDescript ?? ''), 'd0140')
                || str_contains(strtolower($apt->Pattern ?? ''), 'emerg');

            if ($isEmerg) {
                $emergLeadTimes[] = $diffDays;
            }
        }

        $avgLeadTime = count($allLeadTimes) > 0 ? array_sum($allLeadTimes) / count($allLeadTimes) : 0;
        $avgNewPatientLeadTime = count($newPatLeadTimes) > 0 ? array_sum($newPatLeadTimes) / count($newPatLeadTimes) : 0;
        $avgEmergLeadTime = count($emergLeadTimes) > 0 ? array_sum($emergLeadTimes) / count($emergLeadTimes) : 0;

        // Mock tiers heavily matching the requested UI visually
        $data = [
            [
                'location' => $this->clinics->name(0),
                'scheduled_appointments' => $scheduledApts,
                'provider_count' => $providerCount,
                'booked_hours' => number_format($bookedMinutes / 60, 2),
                'avg_lead_all' => number_format($avgLeadTime, 2),
                'avg_lead_new' => number_format($avgNewPatientLeadTime, 2),
                'avg_lead_emerg' => number_format($avgEmergLeadTime, 2),
                '_tiers' => [
                    'scheduled_appointments' => 'top',
                    'provider_count' => 'top',
                    'booked_hours' => 'top',
                    'avg_lead_all' => 'bottom',
                    'avg_lead_new' => 'top',
                    'avg_lead_emerg' => 'top',
                ],
            ],
        ];

        return DataTables::of(collect($data))->make(true);
    }

    public function capacityBreakdown(Request $request)
    {
        $start = $request->get('start') ?? $request->get('date') ?? date('Y-m-d');
        $end = $request->get('end') ?? $start;
        $type = $request->get('type', 'scheduled_appointments');

        $query = OdAppointment::whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->whereIn('AptStatus', [1, 2]);

        if ($type === 'avg_lead_new') {
            $query->whereIn('IsNewPatient', ['1', 1, 'true', true]);
        } elseif ($type === 'avg_lead_emerg') {
            $query->where(function ($q) {
                $q->where('ProcDescript', 'LIKE', '%emergency%')
                    ->orWhere('ProcDescript', 'LIKE', '%D0140%')
                    ->orWhere('Pattern', 'LIKE', '%emerg%');
            });
        }

        $appointments = $query->with(['patient', 'provider'])->get();

        if ($type === 'provider_count') {
            $providers = $appointments->map(function ($apt) {
                $prov = $apt->provider;
                $name = $prov ? trim(($prov->LName ?? '').', '.($prov->FName ?: $prov->PName ?: $prov->Abbr ?: '')) : ('Provider #'.$apt->ProvNum);

                return [
                    'provider' => $name,
                    'provider_name' => $name,
                    'provider_id' => $apt->ProvNum ?: 'N/A',
                ];
            })->unique('provider_id')->values();

            return response()->json($providers);
        }

        $rows = $appointments->map(function ($apt) {
            $pat = $apt->patient;
            $prov = $apt->provider;

            $patName = 'Unknown Patient';
            if ($pat) {
                $formatted = trim(($pat->FName ?? '').' '.($pat->LName ?? ''));
                if (empty($formatted)) {
                    $formatted = trim(($pat->LName ?? '').', '.($pat->FName ?? ''));
                }
                if (! empty($formatted)) {
                    $patName = $formatted;
                }
            }

            $provName = $prov ? trim(($prov->LName ?? '').', '.($prov->FName ?: $prov->PName ?: $prov->Abbr ?: '')) : ('Provider #'.$apt->ProvNum);

            $aptDate = substr(str_replace('T', ' ', $apt->AptDateTime), 0, 10);

            $patternLen = strlen($apt->Pattern ?? '');
            $durationHrs = number_format(($patternLen > 0 ? $patternLen * 5 : 60) / 60, 2);

            $createdDt = new Carbon($apt->SecDateTEdit ?? $apt->DateTStamp ?? $apt->AptDateTime);
            $aptDt = new Carbon($apt->AptDateTime);
            $leadDays = number_format(max(0, $createdDt->diffInDays($aptDt)), 2);

            return [
                'patient' => $patName,
                'patient_id' => $apt->PatNum,
                'date' => $aptDate,
                'duration' => $durationHrs,
                'lead_time' => $leadDays,
                'provider' => $provName,
                'provider_id' => $apt->ProvNum ?: 'N/A',
            ];
        })->values();

        return response()->json($rows);
    }

    public function scheduledProductionBreakdown(Request $request)
    {
        $date = $request->get('date') ?? date('Y-m-d');

        $scheduledAppointments = OdAppointment::query()
            ->with(['patient', 'provider', 'procedureLogs'])
            ->where('AptStatus', '1')
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) = ?", [$date])
            ->get();

        $totalScheduled = 0;
        $providerTotals = [];
        $procedureTotals = [];
        $itemizedApts = [];

        foreach ($scheduledAppointments as $apt) {
            $aptFee = (float) $apt->procedureLogs->sum('ProcFee');
            $totalScheduled += $aptFee;

            $provNum = $apt->ProvNum ? (int) $apt->ProvNum : 0;
            $provName = $apt->provider
                ? trim(($apt->provider->LName ?? '').', '.($apt->provider->PName ?? ''))
                : 'Unassigned';

            if (! isset($providerTotals[$provNum])) {
                $providerTotals[$provNum] = [
                    'id' => $provNum,
                    'name' => $provName,
                    'abbr' => $apt->provider?->Abbr ?? 'N/A',
                    'count' => 0,
                    'total' => 0,
                ];
            }
            $providerTotals[$provNum]['count']++;
            $providerTotals[$provNum]['total'] += $aptFee;

            foreach ($apt->procedureLogs as $log) {
                $desc = trim($log->ProcDescript ?? '') ?: 'Procedure #'.($log->ProcNum ?? '');
                $fee = (float) ($log->ProcFee ?? 0);

                if (! isset($procedureTotals[$desc])) {
                    $procedureTotals[$desc] = [
                        'code' => $desc,
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                $procedureTotals[$desc]['count']++;
                $procedureTotals[$desc]['total'] += $fee;
            }

            $itemizedApts[] = [
                'apt_num' => $apt->AptNum,
                'patient_name' => $apt->patient?->full_name ?? 'Unknown Patient',
                'pat_num' => $apt->PatNum,
                'time' => (new Carbon($apt->AptDateTime))->format('h:i A'),
                'operatory' => 'DR-'.($apt->Op ?? ''),
                'provider' => $provName,
                'procedures' => $apt->ProcDescript ?: 'No procedures specified',
                'fee' => $aptFee,
            ];
        }

        usort($providerTotals, fn ($a, $b) => $b['total'] <=> $a['total']);
        usort($procedureTotals, fn ($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'date' => (new Carbon($date))->format('M d, Y'),
            'total_scheduled' => $totalScheduled,
            'appointment_count' => $scheduledAppointments->count(),
            'by_provider' => array_values($providerTotals),
            'by_procedure' => array_values($procedureTotals),
            'appointments' => $itemizedApts,
        ]);
    }

    public function monthlySummary(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-01');
        $end = $request->get('end') ?? date('Y-m-t');

        // 1. Gross production per date
        $grossByDate = OdProcedureLog::query()
            ->whereIn('ProcStatus', ['C', '2'])
            ->whereRaw("DATE(REPLACE(ProcDate, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(ProcDate, 'T', ' ')) as date_str, COALESCE(SUM(CAST(ProcFee AS DECIMAL(12,2))), 0) AS total")
            ->groupBy('date_str')
            ->pluck('total', 'date_str');

        // 2. Adjustments per date
        $adjByDate = OdAdjustment::query()
            ->whereRaw("DATE(REPLACE(AdjDate, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(AdjDate, 'T', ' ')) as date_str, COALESCE(SUM(CAST(AdjAmt AS DECIMAL(12,2))), 0) AS total")
            ->groupBy('date_str')
            ->pluck('total', 'date_str');

        // 3. Writeoffs per date
        $woByDate = DB::table('od_claim_procs as c')
            ->whereRaw("DATE(REPLACE(c.ProcDate, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(c.ProcDate, 'T', ' ')) as date_str, COALESCE(SUM(CAST(c.WriteOff AS DECIMAL(12,2))), 0) AS total")
            ->groupBy('date_str')
            ->pluck('total', 'date_str');

        // 3. Scheduled production per date
        $schedByDate = OdAppointment::query()
            ->join('od_procedure_logs as pl', 'pl.AptNum', '=', 'od_appointments.AptNum')
            ->where('od_appointments.AptStatus', '1')
            ->whereRaw("DATE(REPLACE(od_appointments.AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(od_appointments.AptDateTime, 'T', ' ')) as date_str, COALESCE(SUM(CAST(pl.ProcFee AS DECIMAL(12,2))), 0) AS total")
            ->groupBy('date_str')
            ->pluck('total', 'date_str');

        // 4. Appointments count per date
        $aptsByDate = OdAppointment::query()
            ->whereIn('AptStatus', [1, 2, 4, 5])
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) as date_str, COUNT(*) as total_apts")
            ->groupBy('date_str')
            ->pluck('total_apts', 'date_str');

        // 5. New Patients (first completed procedure cohort) per date
        $newPtsByDate = DB::table('od_procedure_logs as pl')
            ->joinSub(
                $this->patients->firstVisitCohort(),
                'fv',
                'pl.PatNum',
                '=',
                'fv.PatNum'
            )
            ->whereIn('pl.ProcStatus', ['C', '2'])
            ->whereRaw("DATE(REPLACE(pl.ProcDate, 'T', ' ')) = DATE(REPLACE(fv.first_date, 'T', ' '))")
            ->whereRaw("DATE(REPLACE(pl.ProcDate, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->selectRaw("DATE(REPLACE(pl.ProcDate, 'T', ' ')) as date_str, COUNT(DISTINCT pl.PatNum) as total")
            ->groupBy('date_str')
            ->pluck('total', 'date_str');

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        // Precalculate weekdays in months covering the range
        $weekdayCounts = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $monthKey = $curr->format('Y-m');
            if (! isset($weekdayCounts[$monthKey])) {
                $daysInMonth = $curr->daysInMonth;
                $weekdays = 0;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dayDt = Carbon::createFromDate($curr->year, $curr->month, $d);
                    if ($dayDt->isWeekday()) {
                        $weekdays++;
                    }
                }
                $weekdayCounts[$monthKey] = max(1, $weekdays);
            }
            $curr->addDay();
        }

        $result = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateStr = $cursor->format('Y-m-d');
            $monthKey = $cursor->format('Y-m');
            $weekdaysInMonth = $weekdayCounts[$monthKey] ?? 22;

            $gross = (float) ($grossByDate[$dateStr] ?? 0);
            $adj = (float) ($adjByDate[$dateStr] ?? 0);
            $wo = (float) ($woByDate[$dateStr] ?? 0);
            $prod = $this->production->netFrom($gross, $adj, $wo);
            $sched = $gross;

            $aptsCount = (int) ($aptsByDate[$dateStr] ?? 0);
            $newPtsCount = (int) ($newPtsByDate[$dateStr] ?? 0);

            $goal = $cursor->isWeekday() ? round(100000 / $weekdaysInMonth, 2) : 0.0;

            $result[$dateStr] = [
                'appointments' => $aptsCount,
                'new_pts' => $newPtsCount,
                'sched' => $sched,
                'goal' => $goal,
                'prod' => $prod,
            ];

            $cursor->addDay();
        }

        return response()->json($result);
    }
}
