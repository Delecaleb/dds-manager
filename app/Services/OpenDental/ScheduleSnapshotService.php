<?php

namespace App\Services\OpenDental;

use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleSnapshotService
{
    /**
     * Determine if a date should be locked based on 8:00 AM EST (America/New_York) rule.
     */
    public function shouldDateBeLocked(string $date): bool
    {
        $nowEst = Carbon::now('America/New_York');
        $todayEst = $nowEst->toDateString();

        if ($date < $todayEst) {
            return true;
        }

        if ($date === $todayEst) {
            return $nowEst->hour >= 8;
        }

        return false;
    }

    /**
     * Take a daily snapshot for a specific date and office.
     *
     * @param  int  $officeId  Office location ID
     * @param  string  $date  'Y-m-d'
     * @param  bool  $force  If true, force overwrite even if already locked
     * @return array{status: string, message: string, date: string, office_id: int, locked: bool, metrics: array}
     */
    public function takeSnapshot(int $officeId, string $date, bool $force = false): array
    {
        $shouldLock = $this->shouldDateBeLocked($date);

        // Check if existing snapshot is locked
        $existingLocked = DB::table('od_daily_schedule_snapshots')
            ->where('office_id', $officeId)
            ->where('snapshot_date', $date)
            ->where('is_locked', true)
            ->exists();

        if ($existingLocked && ! $force) {
            return [
                'status' => 'skipped',
                'message' => "Snapshot for office {$officeId} on {$date} is locked and immutable.",
                'date' => $date,
                'office_id' => $officeId,
                'locked' => true,
                'metrics' => [],
            ];
        }

        $nowTimestamp = Carbon::now();

        // 1. Fetch appointments for that date (excluding Planned appointments AptStatus = 6)
        $appointments = DB::table('od_appointments')
            ->where('office_id', $officeId)
            ->whereNotIn('AptStatus', [6])
            ->whereBetween('AptDateTime', ["{$date} 00:00:00", "{$date} 23:59:59"])
            ->get();

        $aptNums = $appointments->pluck('AptNum')->filter()->unique()->toArray();

        // 2. Attached procedure fees for scheduled appointments
        $attachedFees = [];
        if (! empty($aptNums)) {
            $attachedFees = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('AptNum', $aptNums)
                ->groupBy('AptNum')
                ->select('AptNum', DB::raw('SUM(ProcFee) as total_fee'))
                ->pluck('total_fee', 'AptNum')
                ->map(fn ($v) => (float) $v)
                ->toArray();
        }

        // 3. Unscheduled treatment plan balances for scheduled patients
        $patNums = $appointments->pluck('PatNum')->filter()->unique()->toArray();
        $unschedTxByPat = [];
        if (! empty($patNums)) {
            $unschedTxByPat = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $patNums)
                ->whereIn('ProcStatus', [1, '1', 6, '6', 'TP'])
                ->whereRaw('(AptNum IS NULL OR AptNum = 0)')
                ->where('ProcDate', '<=', $date)
                ->groupBy('PatNum')
                ->select('PatNum', DB::raw('SUM(ProcFee) as total_fee'))
                ->pluck('total_fee', 'PatNum')
                ->map(fn ($v) => (float) $v)
                ->toArray();
        }

        // 4. Open appointment hours (Sched hours - Booked appointment minutes)
        $schedRows = DB::table('od_schedules')
            ->where('office_id', $officeId)
            ->where('SchedType', 1)
            ->where('SchedDate', $date)
            ->select('ClinicNum', 'StartTime', 'StopTime')
            ->get();

        $schedMinsByClinic = [];
        foreach ($schedRows as $s) {
            $cNum = (int) ($s->ClinicNum ?? 0);
            $startSec = strtotime('1970-01-01 '.(string) $s->StartTime);
            $stopSec = strtotime('1970-01-01 '.(string) $s->StopTime);
            $mins = max(0, ($stopSec - $startSec) / 60);
            $schedMinsByClinic[$cNum] = ($schedMinsByClinic[$cNum] ?? 0) + $mins;
        }

        $apptMinsByClinic = [];
        foreach ($appointments->whereIn('AptStatus', [1, 2]) as $apt) {
            $cNum = (int) ($apt->ClinicNum ?? 0);
            $pattern = (string) ($apt->Pattern ?? '');
            $duration = strlen($pattern) > 0 ? strlen($pattern) * 5 : 60;
            $apptMinsByClinic[$cNum] = ($apptMinsByClinic[$cNum] ?? 0) + $duration;
        }

        // Determine all clinic numbers to record summary for
        $clinicNums = array_unique(array_merge(
            [0],
            $appointments->pluck('ClinicNum')->map(fn ($c) => (int) $c)->toArray(),
            array_keys($schedMinsByClinic)
        ));

        // Prepare appointment detail records
        $detailRows = [];
        $patSeenByClinic = [];
        $newPatSeenByClinic = [];
        $prodByClinic = [];
        $unschedByClinic = [];

        foreach ($appointments as $apt) {
            $aptNum = (int) $apt->AptNum;
            $patNum = (int) $apt->PatNum;
            $provNum = (int) ($apt->ProvNum ?? 0);
            $clinicNum = (int) ($apt->ClinicNum ?? 0);
            $status = (int) $apt->AptStatus;
            $isNewPat = (bool) ($apt->IsNewPatient ?? false);
            $fee = (float) ($attachedFees[$aptNum] ?? 0.0);
            $unsched = (float) ($unschedTxByPat[$patNum] ?? 0.0);

            $detailRows[] = [
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
                'snapshot_date' => $date,
                'apt_num' => $aptNum,
                'pat_num' => $patNum,
                'prov_num' => $provNum,
                'apt_date_time' => $apt->AptDateTime,
                'apt_status' => $status,
                'pattern' => $apt->Pattern,
                'is_new_patient' => $isNewPat,
                'proc_descript' => $apt->ProcDescript,
                'sched_production' => $fee,
                'unscheduled_tx' => $unsched,
                'is_locked' => $shouldLock,
                'snapshot_taken_at' => $nowTimestamp,
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ];

            // Summary aggregations
            $prodByClinic[$clinicNum] = ($prodByClinic[$clinicNum] ?? 0.0) + $fee;
            $patSeenByClinic[$clinicNum][$patNum] = true;
            if ($isNewPat && in_array($status, [1, 2])) {
                $newPatSeenByClinic[$clinicNum][$patNum] = true;
            }
            if ($unsched > 0) {
                $unschedByClinic[$clinicNum][$patNum] = $unsched;
            }
        }

        // Execute Database Save in a Transaction
        DB::transaction(function () use (
            $officeId,
            $date,
            $force,
            $detailRows,
            $clinicNums,
            $prodByClinic,
            $patSeenByClinic,
            $newPatSeenByClinic,
            $schedMinsByClinic,
            $apptMinsByClinic,
            $unschedByClinic,
            $shouldLock,
            $nowTimestamp
        ) {
            // Delete existing records if force or if unlocked
            $deleteDetailQuery = DB::table('od_appointment_schedule_snapshots')
                ->where('office_id', $officeId)
                ->where('snapshot_date', $date);

            $deleteDailyQuery = DB::table('od_daily_schedule_snapshots')
                ->where('office_id', $officeId)
                ->where('snapshot_date', $date);

            if (! $force) {
                $deleteDetailQuery->where('is_locked', false);
                $deleteDailyQuery->where('is_locked', false);
            }

            $deleteDetailQuery->delete();
            $deleteDailyQuery->delete();

            // Insert new appointment detail records in chunks
            if (! empty($detailRows)) {
                foreach (array_chunk($detailRows, 250) as $chunk) {
                    DB::table('od_appointment_schedule_snapshots')->insert($chunk);
                }
            }

            // Insert daily summary records per clinic
            $dailyRows = [];
            foreach ($clinicNums as $cNum) {
                $sMins = (float) ($schedMinsByClinic[$cNum] ?? 0);
                $bMins = (float) ($apptMinsByClinic[$cNum] ?? 0);
                $openHours = $sMins > 0 ? max(0, round(($sMins - $bMins) / 60, 2)) : 0.0;

                $ptsCount = count($patSeenByClinic[$cNum] ?? []);
                $newPtsCount = count($newPatSeenByClinic[$cNum] ?? []);
                $prod = (float) ($prodByClinic[$cNum] ?? 0.0);
                $unschedSum = array_sum($unschedByClinic[$cNum] ?? []);

                $dailyRows[] = [
                    'office_id' => $officeId,
                    'clinic_num' => $cNum,
                    'snapshot_date' => $date,
                    'sched_production' => $prod,
                    'sched_pts_visit' => $ptsCount,
                    'sched_new_pts_visit' => $newPtsCount,
                    'open_appt_hours' => $openHours,
                    'unscheduled_tx' => $unschedSum,
                    'is_locked' => $shouldLock,
                    'snapshot_taken_at' => $nowTimestamp,
                    'created_at' => $nowTimestamp,
                    'updated_at' => $nowTimestamp,
                ];
            }

            if (! empty($dailyRows)) {
                DB::table('od_daily_schedule_snapshots')->insert($dailyRows);
            }
        });

        return [
            'status' => 'success',
            'message' => 'Snapshot captured successfully.',
            'date' => $date,
            'office_id' => $officeId,
            'locked' => $shouldLock,
            'appointments_count' => count($detailRows),
        ];
    }

    /**
     * Capture or lock today's snapshot for the office.
     */
    public function snapshotToday(int $officeId, bool $force = false): array
    {
        $todayEst = Carbon::now('America/New_York')->toDateString();

        return $this->takeSnapshot($officeId, $todayEst, $force);
    }

    /**
     * Refresh rolling future dates (e.g. today to +60 days) to keep upcoming forecasts live.
     */
    public function syncFutureSnapshots(int $officeId, int $daysAhead = 60): int
    {
        $nowEst = Carbon::now('America/New_York');
        $processed = 0;

        for ($i = 0; $i <= $daysAhead; $i++) {
            $d = $nowEst->copy()->addDays($i)->toDateString();
            $result = $this->takeSnapshot($officeId, $d, false);
            if ($result['status'] === 'success') {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Backfill snapshots for a historical date range.
     */
    public function backfillPastSnapshots(int $officeId, string $startDate, string $endDate, bool $force = false): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $processed = 0;

        $curr = $start->copy();
        while ($curr->lte($end)) {
            $d = $curr->toDateString();
            $result = $this->takeSnapshot($officeId, $d, $force);
            if ($result['status'] === 'success') {
                $processed++;
            }
            $curr->addDay();
        }

        return $processed;
    }

    /**
     * Get snapshot summary data for a date range and optional clinic filter.
     *
     * @param  int[]  $clinics
     * @return array<string, array{sched_production: float, sched_pts_visit: int, sched_new_pts_visit: int, open_appt_hours: float, unscheduled_tx: float}>
     */
    public function getSnapshotSummary(int $officeId, string $startDate, string $endDate, array $clinics = []): array
    {
        $query = DB::table('od_daily_schedule_snapshots')
            ->where('office_id', $officeId)
            ->whereBetween('snapshot_date', [$startDate, $endDate]);

        if (! empty($clinics)) {
            $query->whereIn('clinic_num', $clinics);
        }

        $rows = $query->select(
            'snapshot_date',
            DB::raw('SUM(sched_production) as sched_production'),
            DB::raw('SUM(sched_pts_visit) as sched_pts_visit'),
            DB::raw('SUM(sched_new_pts_visit) as sched_new_pts_visit'),
            DB::raw('SUM(open_appt_hours) as open_appt_hours'),
            DB::raw('SUM(unscheduled_tx) as unscheduled_tx')
        )
            ->groupBy('snapshot_date')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $d = substr((string) $r->snapshot_date, 0, 10);
            $result[$d] = [
                'sched_production' => (float) $r->sched_production,
                'sched_pts_visit' => (int) $r->sched_pts_visit,
                'sched_new_pts_visit' => (int) $r->sched_new_pts_visit,
                'open_appt_hours' => (float) $r->open_appt_hours,
                'unscheduled_tx' => (float) $r->unscheduled_tx,
            ];
        }

        return $result;
    }

    /**
     * Check if snapshot data is available for a date range.
     */
    public function hasSnapshots(int $officeId, string $startDate, string $endDate): bool
    {
        return DB::table('od_daily_schedule_snapshots')
            ->where('office_id', $officeId)
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->exists();
    }
}
