<?php

namespace App\Http\Controllers;

use App\Models\OdPatientBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgingController extends Controller
{
    public function index()
    {
        return view('aging.index');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 20);
        $search = $request->input('search.value', '');

        // ── Count (no LEFT JOIN — avoids row multiplication from family members) ──
        $countBase = OdPatientBalance::query()
            ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
            ->whereNotNull('od_patient_balances.PatNum')
            ->where('od_patient_balances.Total', '!=', 0);

        $totalRecords = (clone $countBase)->count();

        if ($search) {
            $countBase->where(function ($q) use ($search) {
                $q->where('g.LName', 'like', "%{$search}%")
                  ->orWhere('g.FName', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $search ? (clone $countBase)->count() : $totalRecords;

        // ── Data (LEFT JOIN family members, GROUP_CONCAT for display) ────────────
        $dataQuery = OdPatientBalance::query()
            ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
            ->leftJoin('od_patients as m', 'm.Guarantor', '=', 'od_patient_balances.PatNum')
            ->whereNotNull('od_patient_balances.PatNum')
            ->where('od_patient_balances.Total', '!=', 0)
            ->select([
                'od_patient_balances.id',
                'od_patient_balances.PatNum as guarantor_id',
                'g.LName as guarantor_lname',
                'g.FName as guarantor_fname',
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(m.LName, ', ', m.FName) ORDER BY m.LName SEPARATOR ' | ') as family_names"),
                DB::raw("GROUP_CONCAT(DISTINCT m.PatNum ORDER BY m.LName SEPARATOR ', ') as family_ids"),
                'od_patient_balances.Bal_0_30',
                'od_patient_balances.Bal_31_60',
                'od_patient_balances.Bal_61_90',
                'od_patient_balances.BalOver90',
                'od_patient_balances.Total',
            ])
            ->groupBy([
                'od_patient_balances.id',
                'od_patient_balances.PatNum',
                'g.LName',
                'g.FName',
                'od_patient_balances.Bal_0_30',
                'od_patient_balances.Bal_31_60',
                'od_patient_balances.Bal_61_90',
                'od_patient_balances.BalOver90',
                'od_patient_balances.Total',
            ]);

        if ($search) {
            $dataQuery->where(function ($q) use ($search) {
                $q->where('g.LName', 'like', "%{$search}%")
                  ->orWhere('g.FName', 'like', "%{$search}%");
            });
        }

        $records = $dataQuery
            ->orderByDesc('od_patient_balances.Total')
            ->skip($start)
            ->take($length)
            ->get();

        // ── Column totals across the filtered set (not just the current page) ───
        $totals = OdPatientBalance::query()
            ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
            ->whereNotNull('od_patient_balances.PatNum')
            ->where('od_patient_balances.Total', '!=', 0)
            ->when($search, fn ($q) => $q->where(fn ($s) =>
                $s->where('g.LName', 'like', "%{$search}%")
                  ->orWhere('g.FName', 'like', "%{$search}%")
            ))
            ->selectRaw('
                SUM(od_patient_balances.Bal_0_30)  as current_total,
                SUM(od_patient_balances.Bal_31_60) as thirty_total,
                SUM(od_patient_balances.Bal_61_90) as sixty_total,
                SUM(od_patient_balances.BalOver90) as ninety_total,
                SUM(od_patient_balances.Total)     as grand_total
            ')
            ->first();

        $fmt = fn ($v) => '$ ' . number_format((float) ($v ?? 0), 2);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $records->map(fn ($r) => [
                'guarantor_id'   => $r->guarantor_id,
                'guarantor_name' => $r->guarantor_lname . ', ' . $r->guarantor_fname,
                'family_names'   => $r->family_names ?: ($r->guarantor_lname . ', ' . $r->guarantor_fname),
                'family_ids'     => $r->family_ids   ?: (string) $r->guarantor_id,
                'bal_current'    => $fmt($r->Bal_0_30),
                'bal_30'         => $fmt($r->Bal_31_60),
                'bal_60'         => $fmt($r->Bal_61_90),
                'bal_90'         => $fmt($r->BalOver90),
                'total'          => $fmt($r->Total),
            ]),
            'totals' => [
                'current' => $fmt($totals->current_total),
                'thirty'  => $fmt($totals->thirty_total),
                'sixty'   => $fmt($totals->sixty_total),
                'ninety'  => $fmt($totals->ninety_total),
                'grand'   => $fmt($totals->grand_total),
            ],
        ]);
    }
}
