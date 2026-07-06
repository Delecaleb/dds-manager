<?php

namespace App\Http\Controllers;

use App\Models\OdPatient;
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
        $draw = (int) $request->get('draw', 1);
        $start = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 20);
        $search = $request->input('search.value', '');
        $mode = $request->get('mode', 'responsible_party');

        if ($mode === 'by_office') {
            $totals = OdPatientBalance::query()
                ->where('od_patient_balances.Total', '!=', 0)
                ->selectRaw('
                    SUM(od_patient_balances.Bal_0_30)  as current_total,
                    SUM(od_patient_balances.Bal_31_60) as thirty_total,
                    SUM(od_patient_balances.Bal_61_90) as sixty_total,
                    SUM(od_patient_balances.BalOver90) as ninety_total,
                    SUM(od_patient_balances.Total)     as grand_total
                ')->first();

            $fmt = fn($v) => '$ ' . number_format((float) ($v ?? 0), 2);
            $zero = $fmt(0);

            $parentRow = [
                'row_number' => '1',
                'location_name' => '8 Mile',
                'is_parent' => true,
                'bal_current' => $fmt($totals->current_total),
                'bal_30' => $fmt($totals->thirty_total),
                'bal_60' => $fmt($totals->sixty_total),
                'bal_90' => $fmt($totals->ninety_total),
                'bal_120' => $zero,
                'bal_180' => $zero,
                'bal_240' => $zero,
                'bal_365' => $zero,
                'credit_balance' => $zero,
                'contract' => $zero,
                'total' => $fmt($totals->grand_total),
            ];

            $patientRow = array_merge($parentRow, [
                'row_number' => '',
                'location_name' => '- Patient',
                'is_parent' => false,
            ]);

            $insuranceRow = [
                'row_number' => '',
                'location_name' => '- Insurance',
                'is_parent' => false,
                'bal_current' => $zero,
                'bal_30' => $zero,
                'bal_60' => $zero,
                'bal_90' => $zero,
                'bal_120' => $zero,
                'bal_180' => $zero,
                'bal_240' => $zero,
                'bal_365' => $zero,
                'credit_balance' => $zero,
                'contract' => $zero,
                'total' => $zero,
            ];

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 3,
                'recordsFiltered' => 3,
                'data' => [$parentRow, $patientRow, $insuranceRow],
                'totals' => [
                    'current' => $fmt($totals->current_total),
                    'thirty' => $fmt($totals->thirty_total),
                    'sixty' => $fmt($totals->sixty_total),
                    'ninety' => $fmt($totals->ninety_total),
                    'one_twenty' => $zero,
                    'one_eighty' => $zero,
                    'two_forty' => $zero,
                    'three_sixty_five' => $zero,
                    'credit' => $zero,
                    'contract' => $zero,
                    'total' => $fmt($totals->grand_total),
                ],
            ]);
        }

        if ($mode === 'by_patient') {
            $countBase = OdPatient::query()
                ->from('od_patients as p')
                ->leftJoin('od_patients as g', 'p.Guarantor', '=', 'g.PatNum')
                ->whereNotNull('p.PatNum')
                ->where(function ($q) {
                    $q->where('p.Bal_0_30', '!=', 0)
                        ->orWhere('p.Bal_31_60', '!=', 0)
                        ->orWhere('p.Bal_61_90', '!=', 0)
                        ->orWhere('p.BalOver90', '!=', 0)
                        ->orWhere(function ($q2) {
                            $q2->whereRaw("COALESCE(NULLIF(p.BalTotal, ''), 0) != 0");
                        });
                });
        } else {
            $countBase = OdPatientBalance::query()
                ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
                ->whereNotNull('od_patient_balances.PatNum')
                ->where('od_patient_balances.Total', '!=', 0);
        }

        $totalRecords = (clone $countBase)->count();

        if ($search) {
            $countBase->where(function ($q) use ($search, $mode) {
                if ($mode === 'by_patient') {
                    $q->where('p.LName', 'like', "%{$search}%")
                        ->orWhere('p.FName', 'like', "%{$search}%")
                        ->orWhere('g.LName', 'like', "%{$search}%")
                        ->orWhere('g.FName', 'like', "%{$search}%");
                } else {
                    $q->where('g.LName', 'like', "%{$search}%")
                        ->orWhere('g.FName', 'like', "%{$search}%");
                }
            });
        }

        $filteredRecords = $search ? (clone $countBase)->count() : $totalRecords;

        // ── Data (responsible-party or by-patient view) ─────────────────────────
        if ($mode === 'by_patient') {
            $dataQuery = OdPatient::query()
                ->from('od_patients as p')
                ->leftJoin('od_patients as g', 'p.Guarantor', '=', 'g.PatNum')
                ->whereNotNull('p.PatNum')
                ->where(function ($q) {
                    $q->where('p.Bal_0_30', '!=', 0)
                        ->orWhere('p.Bal_31_60', '!=', 0)
                        ->orWhere('p.Bal_61_90', '!=', 0)
                        ->orWhere('p.BalOver90', '!=', 0)
                        ->orWhereRaw("COALESCE(NULLIF(p.BalTotal, ''), 0) != 0");
                })
                ->select([
                    DB::raw("COALESCE(g.PatNum, p.PatNum) as guarantor_id"),
                    DB::raw("p.PatNum as patient_id"),
                    DB::raw("COALESCE(CONCAT(g.LName, ', ', g.FName), CONCAT(p.LName, ', ', p.FName)) as guarantor_name"),
                    DB::raw("CONCAT(p.LName, ', ', p.FName) as family_names"),
                    DB::raw("p.PatNum as family_ids"),
                    'p.Bal_0_30',
                    'p.Bal_31_60',
                    'p.Bal_61_90',
                    'p.BalOver90',
                    DB::raw("COALESCE(NULLIF(p.BalTotal, ''), 0) as Total"),
                ]);

            if ($search) {
                $dataQuery->where(function ($q) use ($search) {
                    $q->where('p.LName', 'like', "%{$search}%")
                        ->orWhere('p.FName', 'like', "%{$search}%")
                        ->orWhere('g.LName', 'like', "%{$search}%")
                        ->orWhere('g.FName', 'like', "%{$search}%");
                });
            }
        } else {
            $dataQuery = OdPatientBalance::query()
                ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
                ->leftJoin('od_patients as m', 'm.Guarantor', '=', 'od_patient_balances.PatNum')
                ->whereNotNull('od_patient_balances.PatNum')
                ->where('od_patient_balances.Total', '!=', 0)
                ->select([
                    'od_patient_balances.id',
                    'od_patient_balances.PatNum as guarantor_id',
                    DB::raw("CONCAT(g.LName, ', ', g.FName) as guarantor_name"),
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
        }

        $records = $dataQuery
            ->when($mode === 'by_patient', fn($query) => $query->orderByDesc(DB::raw("COALESCE(NULLIF(p.BalTotal, ''), 0)")))
            ->when($mode !== 'by_patient', fn($query) => $query->orderByDesc('od_patient_balances.Total'))
            ->skip($start)
            ->take($length)
            ->get();

        // ── Column totals across the filtered set (not just the current page) ───
        if ($mode === 'by_patient') {
            $totals = OdPatient::query()
                ->from('od_patients as p')
                ->leftJoin('od_patients as g', 'p.Guarantor', '=', 'g.PatNum')
                ->whereNotNull('p.PatNum')
                ->where(function ($q) {
                    $q->where('p.Bal_0_30', '!=', 0)
                        ->orWhere('p.Bal_31_60', '!=', 0)
                        ->orWhere('p.Bal_61_90', '!=', 0)
                        ->orWhere('p.BalOver90', '!=', 0)
                        ->orWhereRaw("COALESCE(NULLIF(p.BalTotal, ''), 0) != 0");
                })
                ->when($search, fn($q) => $q->where(
                    fn($s) =>
                    $s->where('p.LName', 'like', "%{$search}%")
                        ->orWhere('p.FName', 'like', "%{$search}%")
                        ->orWhere('g.LName', 'like', "%{$search}%")
                        ->orWhere('g.FName', 'like', "%{$search}%")
                ))
                ->selectRaw('
                    SUM(p.Bal_0_30)  as current_total,
                    SUM(p.Bal_31_60) as thirty_total,
                    SUM(p.Bal_61_90) as sixty_total,
                    SUM(p.BalOver90) as ninety_total,
                    SUM(COALESCE(NULLIF(p.BalTotal, \'\'), 0))     as grand_total
                ')
                ->first();
        } else {
            $totals = OdPatientBalance::query()
                ->join('od_patients as g', 'od_patient_balances.PatNum', '=', 'g.PatNum')
                ->whereNotNull('od_patient_balances.PatNum')
                ->where('od_patient_balances.Total', '!=', 0)
                ->when($search, fn($q) => $q->where(
                    fn($s) =>
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
        }

        $fmt = fn($v) => '$ ' . number_format((float) ($v ?? 0), 2);
        $zero = $fmt(0);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $records->map(fn($r) => [
                'guarantor_id' => $r->guarantor_id,
                'patient_id' => $r->patient_id ?? $r->guarantor_id,
                'guarantor_name' => $r->guarantor_name,
                'family_names' => $r->family_names ?: $r->guarantor_name,
                'family_ids' => $r->family_ids ?: (string) $r->guarantor_id,
                'office' => '8 Mile',
                'insurance' => '-',
                'bal_current' => $fmt($r->Bal_0_30),
                'bal_30' => $fmt($r->Bal_31_60),
                'bal_60' => $fmt($r->Bal_61_90),
                'bal_90' => $fmt($r->BalOver90),
                'bal_120' => $zero,
                'bal_180' => $zero,
                'bal_240' => $zero,
                'bal_365' => $zero,
                'credit_balance' => $zero,
                'contract' => $zero,
                'total' => $fmt($r->Total),
            ]),
            'totals' => [
                'current' => $fmt($totals->current_total),
                'thirty' => $fmt($totals->thirty_total),
                'sixty' => $fmt($totals->sixty_total),
                'ninety' => $fmt($totals->ninety_total),
                'one_twenty' => $zero,
                'one_eighty' => $zero,
                'two_forty' => $zero,
                'three_sixty_five' => $zero,
                'credit' => $zero,
                'contract' => $zero,
                'grand' => $fmt($totals->grand_total),
                'total' => $fmt($totals->grand_total),
            ],
        ]);
    }
}
