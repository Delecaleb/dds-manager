<?php

$ctrlFile = 'app/Http/Controllers/OperationsController.php';
$code = file_get_contents($ctrlFile);

// 1. defMap
$code = str_replace(
    "\$defMap = DB::table('od_definitions')->where('Category', 1)->pluck('ItemName', 'DefNum')->toArray();",
    "\$defMap = DB::table('od_definitions')->where('office_id', \$officeId)->where('Category', 1)->pluck('ItemName', 'DefNum')->toArray();",
    $code
);

// 2. Lines 2239-2263
$oldChunk = <<<'PHP'
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
PHP;

$newChunk = <<<'PHP'
            $logsQuery = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
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
                ->where('office_id', $officeId)
                ->select('PatNum', 'AdjAmt')
                ->whereIn('PatNum', $patIds)
                ->whereBetween('AdjDate', [$start, $end]);

            $wosQuery = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->select('PatNum', 'WriteOff')
                ->whereIn('PatNum', $patIds)
                ->whereBetween('ProcDate', [$start, $end]);
PHP;

$code = str_replace($oldChunk, $newChunk, $code);

file_put_contents($ctrlFile, $code);
echo "Cleaned up remaining OperationsController queries.\n";
