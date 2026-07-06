<?php

namespace App\Http\Controllers;

use App\Models\OdProcedureLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TxMinerController extends Controller
{
    public function index()
    {
        return view('tx-miner.index');
    }

    public function data(Request $request)
    {
        $draw = (int) $request->get('draw', 1);
        $start = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 20);

        // Typically month filter or overall
        // Group by month YYYY-MM
        // Since we are returning "By Month", we aggregate over ProcDate or DateTP
        // Note: For Treatment Planned procedures, DateTP or ProcDate might be used.
        // We will group by DATE_FORMAT(ProcDate, '%Y-%m') 

        $query = OdProcedureLog::query()
            ->selectRaw("DATE_FORMAT(ProcDate, '%Y-%m') as month_group")
            // Total TX Plan: procedures with status 'TP' (Treatment Planned)
            ->selectRaw("SUM(CASE WHEN ProcStatus = 'TP' THEN ProcFee ELSE 0 END) as total_tx_plan")
            // Tx Scheduled: TP procedures that have been linked to an appointment (AptNum != 0)
            ->selectRaw("SUM(CASE WHEN ProcStatus = 'TP' AND AptNum != 0 THEN ProcFee ELSE 0 END) as tx_scheduled")
            // Completed: procedures with status 'C' (Complete)
            ->selectRaw("SUM(CASE WHEN ProcStatus = 'C' THEN ProcFee ELSE 0 END) as completed_tx")
            // # TX Plans Presented: count of TP procedures
            ->selectRaw("COUNT(CASE WHEN ProcStatus = 'TP' THEN 1 END) as tx_presented_count")
            // Distinct patients seen (ProcStatus = 'C') — used for 'Patients with Tx Plan %'
            ->selectRaw("COUNT(DISTINCT CASE WHEN ProcStatus = 'C' THEN PatNum END) as patients_seen")
            // Distinct patients with a TP in this month
            ->selectRaw("COUNT(DISTINCT CASE WHEN ProcStatus = 'TP' THEN PatNum END) as patients_with_tp")
            ->whereNotNull('ProcDate')
            ->whereYear('ProcDate', '>=', 2000)
            ->groupBy('month_group')
            ->orderBy('month_group', 'desc');

        $totalRecords = DB::query()->fromSub($query, 'sub')->count();

        $records = $query->skip($start)->take($length)->get();

        $fmt = fn($v) => '$ ' . number_format((float) ($v ?? 0), 2);

        $data = $records->map(function ($r) use ($fmt) {
            $totalTx = (float) $r->total_tx_plan;
            $txScheduled = (float) $r->tx_scheduled;
            $unscheduled = $totalTx - $txScheduled;
            $completed = (float) $r->completed_tx;

            $caseAcceptance = $totalTx > 0
                ? (($completed + $txScheduled) / $totalTx) * 100
                : 0;

            $txPresentedCount = (int) $r->tx_presented_count;
            $avgTxPlan = $txPresentedCount > 0 ? $totalTx / $txPresentedCount : 0;

            $patientsSeen = (int) ($r->patients_seen ?? 0);
            $patientsWithTp = (int) ($r->patients_with_tp ?? 0);
            $patientsTxPct = $patientsSeen > 0
                ? number_format(($patientsWithTp / $patientsSeen) * 100, 1) . '%'
                : '0.0%';

            try {
                $monthLabel = Carbon::createFromFormat('Y-m', $r->month_group)->format('M y');
            } catch (\Exception $e) {
                $monthLabel = $r->month_group;
            }

            return [
                'month' => $monthLabel,
                'total_tx_plan' => $fmt($totalTx),
                'tx_scheduled' => $fmt($txScheduled),
                'tx_unscheduled' => $fmt($unscheduled),
                'completed_tx' => $fmt($completed),
                'case_acceptance' => number_format($caseAcceptance, 2) . '%',
                'tx_presented' => $txPresentedCount,
                'avg_tx_plan' => $fmt($avgTxPlan),
                'patients_with_tx' => $patientsTxPct,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }
}
