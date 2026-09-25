<?php

namespace App\Domain\Support;

use App\Models\KpiConfiguration;
use App\Models\OfficeGoal;
use App\Models\ProviderGoal;
use App\Models\SpecialtyGoal;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GoalService
{
    /**
     * Get the office goal for a specific month and office/clinic.
     */
    public function getOfficeGoal(int $officeId, string $yearMonth, string $goalType = 'monthly', ?int $clinicNum = null): ?OfficeGoal
    {
        $query = OfficeGoal::where('office_id', $officeId)
            ->where('year_month', $yearMonth)
            ->where('goal_type', $goalType);

        if ($clinicNum !== null && $clinicNum > 0) {
            $clinicGoal = (clone $query)->where('clinic_num', $clinicNum)->first();
            if ($clinicGoal) {
                return $clinicGoal;
            }
        }

        return $query->whereNull('clinic_num')->first();
    }

    /**
     * Aggregate office goals for a date range (start_date to end_date).
     *
     * @return array{gross_production: float, net_production: float, collection: float, pts_visits: int, npt_visits: int, hyg_visits: int, ini_bonding: int}
     */
    public function getOfficeGoalForDateRange(int $officeId, string $start, string $end, ?int $clinicNum = null): array
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        $defaultResult = [
            'gross_production' => 0.0,
            'net_production' => 0.0,
            'collection' => 0.0,
            'pts_visits' => 0,
            'npt_visits' => 0,
            'hyg_visits' => 0,
            'ini_bonding' => 0,
        ];

        // Gather all year_months in range
        $period = CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endDate->copy()->startOfMonth());
        $months = [];
        foreach ($period as $dt) {
            $months[] = $dt->format('Y-m');
        }

        if (empty($months)) {
            return $defaultResult;
        }

        // If single month and full month, or range covers month:
        $totalGross = 0.0;
        $totalNet = 0.0;
        $totalCollection = 0.0;
        $totalPts = 0;
        $totalNpt = 0;
        $totalHyg = 0;
        $totalIni = 0;

        foreach ($months as $ym) {
            $goal = $this->getOfficeGoal($officeId, $ym, 'monthly', $clinicNum);
            if ($goal) {
                // If single month and partial range, compute fraction of working days or calendar days
                if (count($months) === 1) {
                    $monthStart = Carbon::parse($ym.'-01')->startOfDay();
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    $daysInMonth = $monthEnd->day;
                    $actualStart = $startDate->greaterThan($monthStart) ? $startDate : $monthStart;
                    $actualEnd = $endDate->lessThan($monthEnd) ? $endDate : $monthEnd;
                    $daysInRange = $actualStart->diffInDays($actualEnd) + 1;

                    $ratio = $daysInMonth > 0 ? min(1.0, max(0.0, $daysInRange / $daysInMonth)) : 1.0;

                    $totalGross += (float) $goal->gross_production * $ratio;
                    $totalNet += (float) $goal->net_production * $ratio;
                    $totalCollection += (float) $goal->collection * $ratio;
                    $totalPts += (int) round((int) $goal->pts_visits * $ratio);
                    $totalNpt += (int) round((int) $goal->npt_visits * $ratio);
                    $totalHyg += (int) round((int) $goal->hyg_visits * $ratio);
                    $totalIni += (int) round((int) $goal->ini_bonding * $ratio);
                } else {
                    $totalGross += (float) $goal->gross_production;
                    $totalNet += (float) $goal->net_production;
                    $totalCollection += (float) $goal->collection;
                    $totalPts += (int) $goal->pts_visits;
                    $totalNpt += (int) $goal->npt_visits;
                    $totalHyg += (int) $goal->hyg_visits;
                    $totalIni += (int) $goal->ini_bonding;
                }
            }
        }

        return [
            'gross_production' => round($totalGross, 2),
            'net_production' => round($totalNet, 2),
            'collection' => round($totalCollection, 2),
            'pts_visits' => $totalPts,
            'npt_visits' => $totalNpt,
            'hyg_visits' => $totalHyg,
            'ini_bonding' => $totalIni,
        ];
    }

    /**
     * Get specialty goal for office/clinic and month.
     */
    public function getSpecialtyGoal(int $officeId, string $yearMonth, string $goalType = 'monthly', ?int $clinicNum = null): ?SpecialtyGoal
    {
        $query = SpecialtyGoal::where('office_id', $officeId)
            ->where('year_month', $yearMonth)
            ->where('goal_type', $goalType);

        if ($clinicNum !== null && $clinicNum > 0) {
            $clinicGoal = (clone $query)->where('clinic_num', $clinicNum)->first();
            if ($clinicGoal) {
                return $clinicGoal;
            }
        }

        return $query->whereNull('clinic_num')->first();
    }

    /**
     * Get provider goal for office/clinic, provider and month.
     */
    public function getProviderGoal(int $officeId, int $provNum, string $yearMonth, string $goalType = 'monthly', ?int $clinicNum = null): ?ProviderGoal
    {
        $query = ProviderGoal::where('office_id', $officeId)
            ->where('prov_num', $provNum)
            ->where('year_month', $yearMonth)
            ->where('goal_type', $goalType);

        if ($clinicNum !== null && $clinicNum > 0) {
            $clinicGoal = (clone $query)->where('clinic_num', $clinicNum)->first();
            if ($clinicGoal) {
                return $clinicGoal;
            }
        }

        $res = $query->whereNull('clinic_num')->first();
        if (! $res) {
            // Check for recurring provider goal across months
            $res = ProviderGoal::where('office_id', $officeId)
                ->where('prov_num', $provNum)
                ->where('recurring', true)
                ->latest()
                ->first();
        }

        return $res;
    }

    /**
     * Save/update office goal record.
     */
    public function saveOfficeGoal(int $officeId, string $yearMonth, array $data, string $goalType = 'monthly', ?int $clinicNum = null): OfficeGoal
    {
        $clinicNumVal = ($clinicNum !== null && $clinicNum > 0) ? $clinicNum : null;

        return OfficeGoal::updateOrCreate(
            [
                'office_id' => $officeId,
                'clinic_num' => $clinicNumVal,
                'year_month' => $yearMonth,
                'goal_type' => $goalType,
            ],
            [
                'gross_production' => (float) ($data['gross_production'] ?? 0),
                'net_production' => (float) ($data['net_production'] ?? 0),
                'collection' => (float) ($data['collection'] ?? 0),
                'pts_visits' => (int) ($data['pts_visits'] ?? 0),
                'npt_visits' => (int) ($data['npt_visits'] ?? 0),
                'ini_bonding' => (int) ($data['ini_bonding'] ?? 0),
                'hyg_visits' => (int) ($data['hyg_visits'] ?? 0),
            ]
        );
    }

    /**
     * Save/update specialty goal record.
     */
    public function saveSpecialtyGoal(int $officeId, string $yearMonth, array $data, string $goalType = 'monthly', ?int $clinicNum = null): SpecialtyGoal
    {
        $clinicNumVal = ($clinicNum !== null && $clinicNum > 0) ? $clinicNum : null;

        return SpecialtyGoal::updateOrCreate(
            [
                'office_id' => $officeId,
                'clinic_num' => $clinicNumVal,
                'year_month' => $yearMonth,
                'goal_type' => $goalType,
            ],
            [
                'doctor' => (float) ($data['doctor'] ?? 0),
                'hygiene' => (float) ($data['hygiene'] ?? 0),
                'oral_surgery' => (float) ($data['oral_surgery'] ?? 0),
                'clear_aligners' => (float) ($data['clear_aligners'] ?? 0),
                'perio' => (float) ($data['perio'] ?? 0),
                'pedo' => (float) ($data['pedo'] ?? 0),
                'endo' => (float) ($data['endo'] ?? 0),
                'ortho' => (float) ($data['ortho'] ?? 0),
                'prostho' => (float) ($data['prostho'] ?? 0),
            ]
        );
    }

    /**
     * Save/update provider goal record.
     */
    public function saveProviderGoal(int $officeId, int $provNum, string $yearMonth, array $data, string $goalType = 'monthly', ?int $clinicNum = null): ProviderGoal
    {
        $clinicNumVal = ($clinicNum !== null && $clinicNum > 0) ? $clinicNum : null;

        return ProviderGoal::updateOrCreate(
            [
                'office_id' => $officeId,
                'clinic_num' => $clinicNumVal,
                'prov_num' => $provNum,
                'year_month' => $yearMonth,
                'goal_type' => $goalType,
            ],
            [
                'provider_name' => $data['provider_name'] ?? null,
                'provider_type' => $data['provider_type'] ?? null,
                'recurring' => (bool) ($data['recurring'] ?? false),
                'production_goal' => (float) ($data['production_goal'] ?? 0),
            ]
        );
    }

    /**
     * Save/update KPI configuration.
     */
    public function saveKpiConfig(array $data, ?int $officeId = null, ?int $clinicNum = null): KpiConfiguration
    {
        return KpiConfiguration::updateOrCreate(
            [
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
                'kpi_key' => $data['kpi_key'],
                'category' => $data['category'] ?? 'main',
            ],
            [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'is_enabled' => (bool) ($data['is_enabled'] ?? true),
                'target_goal' => (float) ($data['target_goal'] ?? 0),
            ]
        );
    }
}
