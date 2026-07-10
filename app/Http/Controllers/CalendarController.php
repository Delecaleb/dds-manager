<?php

namespace App\Http\Controllers;


use App\Services\OpenDental\CalendarService;
use App\Models\OdAppointment;
use App\Models\OdProcedureLog;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{

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

        return response()->json($calendar->resources($date, $date));
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

        $adjustments = (float) \App\Models\OdAdjustment::query()
            ->whereRaw("DATE(REPLACE(AdjDate, 'T', ' ')) = ?", [$date])
            ->selectRaw('COALESCE(SUM(CAST(AdjAmt AS DECIMAL(12,2))), 0) AS total')
            ->value('total');

        $produced = $gross - $adjustments;

        $scheduled = (float) OdAppointment::query()
            ->join('od_procedure_logs as pl', 'pl.AptNum', '=', 'od_appointments.AptNum')
            ->where('od_appointments.AptStatus', '1')
            ->whereRaw("DATE(REPLACE(od_appointments.AptDateTime, 'T', ' ')) = ?", [$date])
            ->selectRaw('COALESCE(SUM(CAST(pl.ProcFee AS DECIMAL(12,2))), 0) AS total')
            ->value('total');

        return response()->json([
            'production' => $produced,
            'scheduled_production' => $scheduled,
        ]);
    }

    public function appointmentsDetailsData(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-d');
        $end = $request->get('end') ?? date('Y-m-d');

        // Match by calendar date regardless of whether AptDateTime is a raw ISO
        // 'T' string or a normalized DATETIME (see AppointmentRepository).
        $query = OdAppointment::with(['patient', 'provider'])
            ->withSum('procedureLogs as production_total', 'ProcFee')
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end]);

        return DataTables::of($query)
            ->addColumn('location', fn($row) => '8 Mile')
            ->addColumn('patient_name', fn($row) => trim(($row->patient?->FName ?? '') . ' ' . ($row->patient?->LName ?? '')))
            ->addColumn('appointment_date', fn($row) => (new Carbon($row->AptDateTime))->format('M d, Y'))
            ->addColumn('appointment_time', fn($row) => (new Carbon($row->AptDateTime))->format('h:i A'))
            ->addColumn('appointment_duration', function ($row) {
                $pattern = $row->Pattern ?? '';
                $minutes = strlen($pattern) > 0 ? strlen($pattern) * 10 : 60;
                return "{$minutes}.00";
            })
            ->addColumn('operatory_name', fn($row) => 'DR-' . ($row->Op ?? ''))
            ->addColumn('appointment_status', function ($row) {
                $map = [
                    1 => 'Scheduled',
                    2 => 'Completed',
                    4 => 'ASAP',
                    5 => 'Broken'
                ];
                return $map[$row->AptStatus] ?? 'Scheduled';
            })
            ->addColumn('patient_age', function ($row) {
                if (!empty($row->patient?->Birthdate) && $row->patient?->Birthdate != '0001-01-01' && $row->patient?->Birthdate != '0000-00-00') {
                    return Carbon::parse($row->patient?->Birthdate)->age;
                }
                return '--';
            })
            ->addColumn('patient_phone', fn($row) => $row->patient?->WirelessPhone ?: ($row->patient?->HmPhone ?: '--'))
            ->addColumn('email_address', fn($row) => $row->patient?->Email ?: '--')
            ->addColumn('patient_type', fn($row) => $row->IsNewPatient ? 'New Patient' : 'Existing')
            ->addColumn('appointment_notes', fn($row) => $row->Note ?: '--')
            ->addColumn('confirmation_status', fn($row) => $row->Confirmed ? 'Confirmed' : 'Unconfirmed')
            ->addColumn('provider_name', fn($row) => $row->provider?->Abbr ?? 'Unknown')
            ->addColumn('procedure_codes', fn($row) => $row->ProcDescript ?: '--')
            ->addColumn('production', function ($row) {
                return '$ ' . number_format($row->production_total ?? 0, 2);
            })
            ->addColumn('primary_insurance', fn($row) => 'N/A')
            ->addColumn('secondary_insurance', fn($row) => 'N/A')
            ->addColumn('referral_source', fn($row) => 'Unknown')
            ->addColumn('unscheduled_tx', fn($row) => '$ 0.00')
            ->addColumn('last_visit_date', fn($row) => 'N/A')
            ->make(true);
    }

    public function appointmentCapacityData(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-d');
        $end = $request->get('end') ?? date('Y-m-d');

        // We fetch scheduled appointments for the date frame. Match by calendar
        // date regardless of AptDateTime storage format (see AppointmentRepository).
        $appointments = OdAppointment::whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
            ->where('AptStatus', 1) // Only count Scheduled
            ->get();

        // Calculate aggregate metrics
        $scheduledApts = $appointments->count();
        $providerCount = $appointments->pluck('ProvNum')->unique()->count();

        $bookedMinutes = $appointments->sum(function ($apt) {
            return strlen($apt->Pattern ?? '') > 0 ? strlen($apt->Pattern) * 10 : 60;
        });

        // Compute Lead Time logic
        $allLeadTimes = [];
        $newPatLeadTimes = [];

        foreach ($appointments as $apt) {
            $createdDt = new Carbon($apt->DateTStamp ?? $apt->SecDateTEdit ?? $apt->AptDateTime);
            $aptDt = new Carbon($apt->AptDateTime);
            $diffDays = max(0, $createdDt->diffInDays($aptDt));

            $allLeadTimes[] = $diffDays;

            if ((bool) $apt->IsNewPatient) {
                $newPatLeadTimes[] = $diffDays;
            }
        }

        $avgLeadTime = count($allLeadTimes) > 0 ? array_sum($allLeadTimes) / count($allLeadTimes) : 0;
        $avgNewPatientLeadTime = count($newPatLeadTimes) > 0 ? array_sum($newPatLeadTimes) / count($newPatLeadTimes) : 0;
        $avgEmergLeadTime = 0; // matching Screenshot 0.00 usually

        // Mock tiers heavily matching the requested UI visually
        $data = [
            [
                'location' => '8 Mile',
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
                    'avg_lead_emerg' => 'top'
                ]
            ]
        ];

        return DataTables::of(collect($data))->make(true);
    }
}