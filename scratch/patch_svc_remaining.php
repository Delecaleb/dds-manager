<?php

$svcFile = 'app/Services/OpenDental/OperationsAnalyticsService.php';
$code = file_get_contents($svcFile);

$replacements = [
    // 1. pdGroupedCollections
    '$q = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) AS total\')' => '$q = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) AS total\')',

    // 2. pdGroupedNewPatients
    '$q = DB::table(\'od_procedure_logs as pl\')
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) AS npt\')' => '$q = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw(\'pl.ClinicNum, COUNT(DISTINCT pl.PatNum) AS npt\')',

    // 3. providerRows: firstProcs, patsCur, patsPrior
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')' => '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')',

    '$patsCur = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start18m, $endStr])' => '$patsCur = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start18m, $endStr])',

    '$patsPrior = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start36m, $start18m])' => '$patsPrior = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start36m, $start18m])',

    // 4. collectionsByClinicProvider: qIns
    '$qIns = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ClinicNum, ProvNum, SUM(InsPayAmt) AS total\')' => '$qIns = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, ProvNum, SUM(InsPayAmt) AS total\')',

    // 5. scheduledHoursByClinicProvider: q
    '$q = DB::table(\'od_schedules\')
            ->where(\'SchedType\', 0)
            ->where(\'ProvNum\', \'>\', 0)
            ->whereBetween(\'SchedDate\', [$start, $end]);' => '$q = DB::table(\'od_schedules\')
            ->where(\'office_id\', $officeId)
            ->where(\'SchedType\', 0)
            ->where(\'ProvNum\', \'>\', 0)
            ->whereBetween(\'SchedDate\', [$start, $end]);',

    // 6. cancellationRows: countAppointments call with '5'
    '$broken = $this->countAppointments($start, $end, $clinics, \'5\');' => '$broken = $this->countAppointments($start, $end, $clinics, \'5\', $officeId);',

    // 7. cancellationRows: brokenApptsQ, feeRows
    '$brokenApptsQ = DB::table(\'od_appointments as a\')
            ->select(\'a.AptNum\', \'a.ClinicNum\')
            ->where(\'a.AptStatus\', \'5\')
            ->whereRaw(\'LEFT(a.AptDateTime, 10) BETWEEN ? AND ?\', [$start, $end]);' => '$brokenApptsQ = DB::table(\'od_appointments as a\')
            ->where(\'a.office_id\', $officeId)
            ->select(\'a.AptNum\', \'a.ClinicNum\')
            ->where(\'a.AptStatus\', \'5\')
            ->whereRaw(\'LEFT(a.AptDateTime, 10) BETWEEN ? AND ?\', [$start, $end]);',

    '$feeRows = DB::table(\'od_procedure_logs\')
                ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                ->whereIn(\'AptNum\', $uniqueAptNums)' => '$feeRows = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->selectRaw(\'AptNum, SUM(ProcFee) as total_fee\')
                ->whereIn(\'AptNum\', $uniqueAptNums)',

    // 8. countAppointments
    '$q = DB::table(\'od_appointments\')
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT AptNum) AS total\')
            ->whereRaw(\'LEFT(AptDateTime, 10) BETWEEN ? AND ?\', [$start, $end]);' => '$q = DB::table(\'od_appointments\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT AptNum) AS total\')
            ->whereRaw(\'LEFT(AptDateTime, 10) BETWEEN ? AND ?\', [$start, $end]);',

    // 9. patientRetentionMetrics: firstProcs, qCur, qPrior
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->groupBy(\'pl.PatNum\')' => '$firstProcs = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')
            ->groupBy(\'pl.PatNum\')',

    '$qCur = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start18m.\' 00:00:00\', $end.\' 23:59:59\']);' => '$qCur = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start18m.\' 00:00:00\', $end.\' 23:59:59\']);',

    '$qPrior = DB::table(\'od_procedure_logs as pl\')
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start36m.\' 00:00:00\', $start18m.\' 00:00:00\']);' => '$qPrior = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
            ->whereBetween(\'pl.ProcDate\', [$start36m.\' 00:00:00\', $start18m.\' 00:00:00\']);',

    // 10. activePatientMetrics: totalBase, activeBase
    '$totalBase = DB::table(\'od_procedure_logs\')
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT PatNum) as total_ever_pts\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed())' => '$totalBase = DB::table(\'od_procedure_logs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, COUNT(DISTINCT PatNum) as total_ever_pts\')
            ->whereIn(\'ProcStatus\', ProcStatus::completed())',

    '$activeBase = DB::table(\'od_procedure_logs as pl\')
            ->leftJoin(\'od_appointments as apt\', function ($join) use ($end) {
                $join->on(\'pl.PatNum\', \'=\', \'apt.PatNum\')
                    ->whereIn(\'apt.AptStatus\', [1, 2])
                    ->where(\'apt.AptDateTime\', \'>\', $end.\' 23:59:59\');
            })' => '$activeBase = DB::table(\'od_procedure_logs as pl\')
            ->leftJoin(\'od_appointments as apt\', function ($join) use ($end, $officeId) {
                $join->on(\'pl.PatNum\', \'=\', \'apt.PatNum\')
                    ->where(\'apt.office_id\', \'=\', $officeId)
                    ->whereIn(\'apt.AptStatus\', [1, 2])
                    ->where(\'apt.AptDateTime\', \'>\', $end.\' 23:59:59\');
            })
            ->where(\'pl.office_id\', $officeId)',

    // 11. collectionsByClinic: qIns
    '$qIns = DB::table(\'od_claim_procs\')
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) AS total\')
            ->whereBetween(\'DateCP\', [$start, $end])' => '$qIns = DB::table(\'od_claim_procs\')
            ->where(\'office_id\', $officeId)
            ->selectRaw(\'ClinicNum, SUM(InsPayAmt) AS total\')
            ->whereBetween(\'DateCP\', [$start, $end])',

    // 12. serviceRows: cats
    '$cats = DB::table(\'od_definitions\')->where(\'Category\', 5)->get()->keyBy(\'DefNum\');' => '$cats = DB::table(\'od_definitions\')->where(\'office_id\', $officeId)->where(\'Category\', 5)->get()->keyBy(\'DefNum\');',

    // 13. calculateTrendMetricBuckets
    // qWd
    '$qWd = DB::table(\'od_procedure_logs as pl\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);' => '$qWd = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$start, $end]);',

    // gross in 5b
    '$gross = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as total_gross")' => '$gross = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as total_gross")',

    // otcColls in 5b
    '$otcColls = DB::table(\'od_pay_splits as ps\')
                ->join(\'od_payments as p\', \'ps.PayNum\', \'=\', \'p.PayNum\')' => '$otcColls = DB::table(\'od_pay_splits as ps\')
                ->join(\'od_payments as p\', function ($j) use ($officeId) { $j->on(\'ps.PayNum\', \'=\', \'p.PayNum\')->where(\'p.office_id\', \'=\', $officeId); })
                ->where(\'ps.office_id\', $officeId)',

    // 7. Cancellation Rate
    '$q = DB::table(\'od_appointments as apt\')
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as total_cnt, SUM(CASE WHEN apt.AptStatus IN (5, 6) THEN 1 ELSE 0 END) as broken_cnt")' => '$q = DB::table(\'od_appointments as apt\')
                ->where(\'apt.office_id\', $officeId)
                ->selectRaw("apt.ClinicNum, {$mAptDate} as month, COUNT(*) as total_cnt, SUM(CASE WHEN apt.AptStatus IN (5, 6) THEN 1 ELSE 0 END) as broken_cnt")',

    // 8. New Patients Visits in trend
    '$firstProcSub = DB::table(\'od_procedure_logs\')
                ->select(\'PatNum\', DB::raw(\'MIN(ProcDate) as first_date\'))' => '$firstProcSub = DB::table(\'od_procedure_logs\')
                ->where(\'office_id\', $officeId)
                ->select(\'PatNum\', DB::raw(\'MIN(ProcDate) as first_date\'))',

    '$q = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcSub, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, COUNT(DISTINCT pl.PatNum) as val")' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->joinSub($firstProcSub, \'fp\', \'pl.PatNum\', \'=\', \'fp.PatNum\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, COUNT(DISTINCT pl.PatNum) as val")
                ->where(\'pl.office_id\', $officeId)',

    // 8b. Patient Retention Trend
    '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
                ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')' => '$firstProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
                ->selectRaw(\'pl.PatNum, MIN(pl.ProcDate) as first_date\')',

    '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
                ->whereBetween(\'pl.ProcDate\', [$earliest36.\' 00:00:00\', $latestEnd.\' 23:59:59\']);' => '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereRaw("COALESCE(pl.CodeNum, \'\') != \'626\'")
                ->whereBetween(\'pl.ProcDate\', [$earliest36.\' 00:00:00\', $latestEnd.\' 23:59:59\']);',

    // 9. Active Patients Count vs Active Patients Percentage
    '$qTotal = DB::table(\'od_procedure_logs as pl\')
                    ->select(\'pl.ClinicNum\', DB::raw(\'COUNT(DISTINCT pl.PatNum) as total_val\'))
                    ->whereIn(\'pl.ProcStatus\', ProcStatus::completed());' => '$qTotal = DB::table(\'od_procedure_logs as pl\')
                    ->where(\'pl.office_id\', $officeId)
                    ->select(\'pl.ClinicNum\', DB::raw(\'COUNT(DISTINCT pl.PatNum) as total_val\'))
                    ->whereIn(\'pl.ProcStatus\', ProcStatus::completed());',

    '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest24.\' 00:00:00\', $latestEnd.\' 23:59:59\']);' => '$qProcs = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->whereIn(\'pl.ProcStatus\', ProcStatus::completed())
                ->whereBetween(\'pl.ProcDate\', [$earliest24.\' 00:00:00\', $latestEnd.\' 23:59:59\']);',

    // 10. Specific Clinical Codes
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_procedures as pc\', \'pc.CodeNum\', \'=\', \'pl.CodeNum\')' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->join(\'od_procedures as pc\', function ($j) use ($officeId) { $j->on(\'pc.CodeNum\', \'=\', \'pl.CodeNum\')->where(\'pc.office_id\', \'=\', $officeId); })
                ->where(\'pl.office_id\', $officeId)',

    // 11. Patient Visits
    '$q = DB::table(\'od_procedure_logs as pl\')
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, ".MetricDefinitions::patientVisits(\'val\'))' => '$q = DB::table(\'od_procedure_logs as pl\')
                ->where(\'pl.office_id\', $officeId)
                ->selectRaw("pl.ClinicNum, {$mProcDate} as month, ".MetricDefinitions::patientVisits(\'val\'))',

    // Default: Net Production (Gross + Adjustments + WriteOffs)
    '$qGross = DB::table(\'od_procedure_logs as pl\')
            ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")' => '$qGross = DB::table(\'od_procedure_logs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.ProcFee) as val")',

    '$qAdj = DB::table(\'od_adjustments as adj\')
            ->selectRaw("adj.ClinicNum, {$mAdjDate} as month, SUM(adj.AdjAmt) as val")' => '$qAdj = DB::table(\'od_adjustments as adj\')
            ->where(\'adj.office_id\', $officeId)
            ->selectRaw("adj.ClinicNum, {$mAdjDate} as month, SUM(adj.AdjAmt) as val")',

    '$qWo = DB::table(\'od_claim_procs as pl\')
            ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.WriteOff) as val")' => '$qWo = DB::table(\'od_claim_procs as pl\')
            ->where(\'pl.office_id\', $officeId)
            ->selectRaw("pl.ClinicNum, {$mProcDate} as month, SUM(pl.WriteOff) as val")',
];

foreach ($replacements as $search => $replace) {
    if (strpos($code, $search) !== false) {
        $code = str_replace($search, $replace, $code);
    } else {
        echo "Warning: target not found:\n".substr($search, 0, 70)."\n";
    }
}

file_put_contents($svcFile, $code);
echo "OperationsAnalyticsService remaining queries patched.\n";
