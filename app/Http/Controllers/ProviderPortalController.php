<?php

namespace App\Http\Controllers;

use App\Domain\Support\ProcStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderPortalController extends Controller
{
    private array $specialtyMap = [
        0 => 'General',
        1 => 'Endodontics',
        2 => 'Orthodontics',
        3 => 'Periodontics',
        4 => 'Prosthetics',
        5 => 'Oral Surgery',
        6 => 'Pediatric',
        7 => 'Denturist',
        8 => 'Hygienist',
        268 => 'Invisalign',
    ];

    /** Pre-rendered completed-status IN-list for raw-SQL heredoc interpolation (DRY). */
    private readonly string $completedIn;

    public function __construct(
        private readonly \App\Domain\Support\ClinicRegistry $clinics,
    ) {
        $this->completedIn = ProcStatus::inList(ProcStatus::completed());
    }

    public function index()
    {
        return view('provider-portal.index');
    }

    public function providers()
    {
        $rows = DB::table('od_providers')
            ->whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->orderBy('PName')
            ->get(['ProvNum', 'LName', 'PName', 'Specialty']);

        return response()->json($rows->map(fn($p) => [
            'id' => (int) $p->ProvNum,
            'name' => trim("{$p->LName}, {$p->PName}"),
            'type' => $this->specialtyMap[(int) $p->Specialty] ?? 'General',
            'is_hyg' => (int) $p->Specialty === 8,
        ])->values());
    }

    public function chart(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $mode = $request->input('mode', 'daily');
        $provs = array_values(array_filter((array) $request->input('providers', [])));
        $type = $request->input('provider_type', 'all');

        [$groupExpr, $labelExpr] = $this->periodExprs($mode, 'pl');

        $bindings = [$start, $end];
        $provFilter = '';
        $typeFilter = '';

        if (!empty($provs)) {
            $ph = implode(',', array_fill(0, count($provs), '?'));
            $provFilter = "AND pl.ProvNum IN ({$ph})";
            array_push($bindings, ...$provs);
        }

        if ($type === 'hygiene') {
            $typeFilter = "AND pc.IsHygiene = 'true'";
        } elseif ($type === 'doctor') {
            $typeFilter = "AND pc.IsHygiene = 'false'";
        }

        $rows = DB::select("
            SELECT {$labelExpr} AS label,
                   COALESCE(SUM(pl.ProcFee), 0) AS production
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              {$provFilter}
              {$typeFilter}
            GROUP BY {$groupExpr}
            ORDER BY {$groupExpr}
        ", $bindings);

        return response()->json(array_map(fn($r) => [
            'label' => $r->label,
            'production' => round((float) $r->production, 2),
        ], $rows));
    }

    public function table(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $mode = $request->input('mode', 'daily');
        $provs = array_values(array_filter((array) $request->input('providers', [])));
        $type = $request->input('provider_type', 'all');

        [$groupExpr, $labelExpr] = $this->periodExprs($mode, 'pl');

        $bindings = [$start, $end];
        $provFilter = '';
        $typeFilter = '';

        if (!empty($provs)) {
            $ph = implode(',', array_fill(0, count($provs), '?'));
            $provFilter = "AND pl.ProvNum IN ({$ph})";
            array_push($bindings, ...$provs);
        }

        if ($type === 'hygiene') {
            $typeFilter = "AND p.Specialty = 8";
        } elseif ($type === 'doctor') {
            $typeFilter = "AND p.Specialty != 8";
        }

        $rows = DB::select("
            SELECT
                p.ProvNum,
                CONCAT(TRIM(p.LName), ', ', TRIM(p.PName)) AS provider_name,
                p.Specialty,
                {$labelExpr} AS period,
                COALESCE(SUM(pl.ProcFee), 0)                                                  AS total_prod,
                COALESCE(SUM(CASE WHEN pc.IsHygiene = 'true' THEN pl.ProcFee ELSE 0 END), 0) AS hyg_prod,
                COUNT(DISTINCT CASE WHEN pc.IsHygiene = 'true' THEN pl.ProcDate END)          AS hyg_days,
                COUNT(DISTINCT CONCAT(pl.PatNum,'-',pl.ProcDate))                             AS visits,
                COUNT(DISTINCT pl.ProcDate)                                                    AS work_days,
                SUM(pc.ProcCode IN ('D9975','D9976','D9972','D9973','D9974'))                  AS whitening,
                SUM(pc.ProcCode IN ('D4921','D4922'))                                          AS irrigation,
                SUM(pc.ProcCode IN ('D1203','D1204','D1206','D1208','D1209'))                  AS fluoride,
                SUM(pc.ProcCode IN ('D1351','D1352'))                                          AS sealants,
                SUM(pc.ProcCode IN ('D4261','D4262','D4268','D6199'))                          AS laser,
                SUM(pc.ProcCode IN ('D1330','D1320'))                                          AS toothbrushes
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_providers p   ON pl.ProvNum  = p.ProvNum
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              AND p.IsHidden IN ('false', '0', 0)
              {$provFilter}
              {$typeFilter}
            GROUP BY p.ProvNum, p.LName, p.PName, p.Specialty, {$groupExpr}
            ORDER BY period DESC, total_prod DESC
        ", $bindings);

        return response()->json(array_map(function ($r) {
            $prodPerVisit = $r->visits > 0 ? round($r->total_prod / $r->visits, 2) : 0;
            $visitsPerDay = $r->work_days > 0 ? round($r->visits / $r->work_days, 2) : 0;
            $avgHygDay = $r->hyg_days > 0 ? round($r->hyg_prod / $r->hyg_days, 2) : 0;
            $wht = (int) $r->whitening;
            $irr = (int) $r->irrigation;
            $flu = (int) $r->fluoride;
            $sea = (int) $r->sealants;
            $las = (int) $r->laser;
            $tbr = (int) $r->toothbrushes;

            return [
                'provider' => $r->provider_name,
                'office' => $this->clinics->name(0),
                'provider_type' => $this->specialtyMap[(int) $r->Specialty] ?? 'General',
                'date' => $r->period,
                'avg_rev_hyg' => $avgHygDay,
                'prod_per_visit' => $prodPerVisit,
                'visits_day' => $visitsPerDay,
                'whitening' => $wht,
                'irrigation' => $irr,
                'fluoride' => $flu,
                'sealants' => $sea,
                'laser' => $las,
                'toothbrushes' => $tbr,
                'adj_total' => $wht + $irr + $flu + $sea + $las + $tbr,
                'retention' => null,
            ];
        }, $rows));
    }

    private function periodExprs(string $mode, string $alias): array
    {
        $d = "{$alias}.ProcDate";
        return match ($mode) {
            'weekly' => ["YEARWEEK({$d}, 1)", "DATE_FORMAT(MIN({$d}), '%Y-%m-%d')"],
            'monthly' => ["DATE_FORMAT({$d}, '%Y-%m')", "DATE_FORMAT({$d}, '%Y-%m')"],
            default => ["DATE_FORMAT({$d}, '%Y-%m-%d')", "DATE_FORMAT({$d}, '%Y-%m-%d')"],
        };
    }
}
