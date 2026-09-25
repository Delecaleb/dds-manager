<?php

namespace App\Domain\Kpi;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use Illuminate\Support\Facades\DB;

/**
 * Row-level detail behind every Hygiene KPI card — the drill-down the card's icon opens.
 *
 * Each method answers "which records produced this number?" for one card, in the column
 * layout Jarvis uses for the same drill-down, so the two can be compared line by line.
 *
 * The card values themselves live in KpisController::hygieneKpiSet(); the procedure-code
 * lists both sides filter on are the constants below, so a code list is changed once.
 */
final class HygieneKpiDetailService
{
    /** Hygiene visit codes: the denominator of Perio % and the definition of a hygiene visit. */
    public const HYGIENE_VISIT_CODES = ['D1110', 'D1120', 'D4341', 'D4342', 'D4910', 'D4346', 'D4355', '1110', '1120', '4341', '4342', '4910', '4346', '4355'];

    /** Perio codes: the numerator of Perio %. */
    public const PERIO_CODES = ['D4341', 'D4342', 'D4910', 'D4346', 'D4355', '4341', '4342', '4910', '4346', '4355'];

    /** Perio reappointment looks at scaling/root planing and perio maintenance only. */
    public const PERIO_REAPPT_CODES = ['D4341', 'D4342', 'D4910', '4341', '4342', '4910'];

    public const FLUORIDE_CODES = ['D1206', 'D1208', '1206', '1208'];

    public const SRP_CODES = ['D4341', 'D4342', '4341', '4342'];

    public const FMX_CODES = ['D0210', '0210'];

    public const SEALANT_CODES = ['D1351'];

    public const WHITENING_CODES = ['D9972', 'D9973', 'D9974', 'D9975'];

    public const ANTIMICROBIAL_CODES = ['D4381'];

    /** Retention counts the recall visit codes only. */
    public const ADULT_RETENTION_CODES = ['D1110', 'D4910', '1110', '4910'];

    public const CHILD_RETENTION_CODES = ['D1120', '1120'];

    /**
     * Every code the "hygiene production" scan counts, matching the card query:
     * anything flagged IsHygiene plus these explicit codes.
     */
    public const HYGIENE_PRODUCTION_CODES = [
        'D1110', 'D1120', 'D4341', 'D4342', 'D4910', 'D4346', 'D4355',
        'D1206', 'D1208', 'D0210', 'D1351', 'D9972', 'D9973', 'D9974', 'D9975', 'D4381',
        '1110', '1120', '4341', '4342', '4910', '4346', '4355',
        '1206', '1208', '0210', '1351', '4381',
    ];

    /** Card key => drill-down title, in card order. A key absent here has no drill-down. */
    private const TITLES = [
        'perio_pct' => 'Perio %',
        'fluoride_per_day' => '# of Fluoride app. per day',
        'avg_prod_per_day' => 'Avg. Prod. per Day',
        'avg_prod_per_prov_day' => 'Avg Production per Provider Per Day',
        'prod_per_visit' => 'Production per patient visit',
        'fmx_per_day' => 'Avg. Fmx per day',
        'srp_per_day' => 'Avg. SRP per day',
        'visits_per_day' => 'Number of visits per day',
        'reappt' => 'Hygiene Reappointment',
        'perio_reappt' => 'Perio Reappointment',
        'adult_retention_12m' => 'Adult Hygiene Retention (12 months)',
        'adult_retention_6m' => 'Adult Hygiene Retention (6 months)',
        'child_retention_12m' => 'Child Hygiene Retention (12 months)',
        'child_retention_6m' => 'Child Hygiene Retention (6 months)',
        'sealants' => 'Sealants',
        'whitening' => 'Whitening Procedures',
        'antimicrobial' => 'Antimicrobial Placement',
        'prod_per_proc' => 'Hygiene Production per Procedure',
        'visits_with_tx_pct' => '% of Hygiene Visits with TX Plan',
        'tx_plans_per_day' => '# of Tx plan per Day',
        'avg_prod_per_hour' => 'Average Hygiene Production per Hour',
        'case_acceptance' => 'Case Acceptance Rate',
    ];

    public function supports(string $metric): bool
    {
        return isset(self::TITLES[$metric]);
    }

    public function title(string $metric): string
    {
        return self::TITLES[$metric] ?? $metric;
    }

    /** @return list<string> */
    public function metrics(): array
    {
        return array_keys(self::TITLES);
    }

    /**
     * The rows behind one card.
     *
     * @return list<array<string, mixed>>
     */
    public function rows(string $metric, MetricFilter $filter): array
    {
        return match ($metric) {
            'perio_pct' => $this->perioByProvider($filter),
            'fluoride_per_day' => $this->procedureDays($filter, self::FLUORIDE_CODES),
            'fmx_per_day' => $this->procedureDays($filter, self::FMX_CODES),
            'srp_per_day' => $this->procedureDays($filter, self::SRP_CODES),
            'avg_prod_per_day' => $this->productionByDay($filter),
            'avg_prod_per_prov_day' => $this->productionByProviderDay($filter),
            'prod_per_visit' => $this->productionByPatient($filter),
            'visits_per_day' => $this->visits($filter),
            'reappt' => $this->reappointments($filter, null),
            'perio_reappt' => $this->reappointments($filter, self::PERIO_REAPPT_CODES),
            'adult_retention_12m' => $this->retention($filter, self::ADULT_RETENTION_CODES, 12, true),
            'adult_retention_6m' => $this->retention($filter, self::ADULT_RETENTION_CODES, 6, true),
            'child_retention_12m' => $this->retention($filter, self::CHILD_RETENTION_CODES, 12, false),
            'child_retention_6m' => $this->retention($filter, self::CHILD_RETENTION_CODES, 6, false),
            'sealants' => $this->procedures($filter, self::SEALANT_CODES),
            'whitening' => $this->procedures($filter, self::WHITENING_CODES),
            'antimicrobial' => $this->procedures($filter, self::ANTIMICROBIAL_CODES),
            'prod_per_proc' => $this->procedures($filter, null),
            'visits_with_tx_pct' => $this->visitsWithTxPlan($filter),
            'tx_plans_per_day' => $this->txPlansByDay($filter),
            'avg_prod_per_hour' => $this->productionPerHourByDay($filter),
            'case_acceptance' => $this->caseAcceptanceByPatient($filter),
            default => [],
        };
    }

    // ── Cards ────────────────────────────────────────────────────────────────

    /** Perio % — perio visits vs hygiene visits, per provider (Jarvis: Provider, Perio, Hygiene, Percent). */
    private function perioByProvider(MetricFilter $filter): array
    {
        $patDate = $this->patDate();
        $perio = $this->inList(self::PERIO_CODES);
        $hygiene = $this->inList(self::HYGIENE_VISIT_CODES);

        $rows = DB::select("
            SELECT pl.ProvNum,
                   COUNT(DISTINCT CASE WHEN pc.ProcCode IN ({$perio})   THEN {$patDate} END) AS perio,
                   COUNT(DISTINCT CASE WHEN pc.ProcCode IN ({$hygiene}) THEN {$patDate} END) AS hygiene
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}
            GROUP BY pl.ProvNum
            ORDER BY pl.ProvNum
        ", $this->bindings($filter));

        $providers = $this->providerNames($filter, array_map(fn ($r) => (int) $r->ProvNum, $rows));

        return array_map(fn ($r) => [
            'Provider Id' => $this->providerLabel($r->ProvNum, $providers),
            'Provider' => $providers[(int) $r->ProvNum]['name'] ?? '',
            'Perio' => (int) $r->perio,
            'Hygiene' => (int) $r->hygiene,
            'Percent' => ((int) $r->hygiene) > 0 ? round((int) $r->perio / (int) $r->hygiene * 100, 2).'%' : '0%',
        ], $rows);
    }

    /**
     * Per-day procedure counts for a code set (Fluoride, FMX, SRP).
     * Jarvis: Date, Procedures, Provider Ids, Providers.
     */
    private function procedureDays(MetricFilter $filter, array $codes): array
    {
        $codeIn = $this->inList($codes);

        $rows = DB::select("
            SELECT pl.ProcDate                AS day,
                   COUNT(DISTINCT pl.PatNum)  AS procedures,
                   {$this->groupConcat('DISTINCT pl.ProvNum')} AS prov_nums
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}
              AND pc.ProcCode IN ({$codeIn})
            GROUP BY pl.ProcDate
            ORDER BY pl.ProcDate
        ", $this->bindings($filter));

        return $this->withProviderColumns($filter, $rows, fn ($r) => [
            'Date' => $r->day,
            'Procedures' => (int) $r->procedures,
        ]);
    }

    /** Avg. Prod. per Day — Jarvis: Date, Production, Working Day. */
    private function productionByDay(MetricFilter $filter): array
    {
        $rows = DB::select("
            SELECT pl.ProcDate AS day, COALESCE(SUM(pl.ProcFee), 0) AS production
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}
            GROUP BY pl.ProcDate
            ORDER BY pl.ProcDate
        ", $this->bindings($filter));

        return array_map(fn ($r) => [
            'Date' => $r->day,
            'Production' => round((float) $r->production, 2),
            // A day appears here only because it carries hygiene production, which is
            // exactly what the card counts as a working day.
            'Working Day' => 'Yes',
        ], $rows);
    }

    /** Avg Production per Provider Per Day — Jarvis: Provider, Working Days, Production, Avg, Working Dates. */
    private function productionByProviderDay(MetricFilter $filter): array
    {
        $rows = DB::select("
            SELECT pl.ProvNum,
                   COUNT(DISTINCT pl.ProcDate)   AS days,
                   COALESCE(SUM(pl.ProcFee), 0)  AS production,
                   {$this->groupConcat('DISTINCT pl.ProcDate')} AS dates
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneOnly($filter)}
            GROUP BY pl.ProvNum
            ORDER BY pl.ProvNum
        ", $this->bindings($filter));

        $providers = $this->providerNames($filter, array_map(fn ($r) => (int) $r->ProvNum, $rows));

        return array_map(function ($r) use ($providers) {
            $days = (int) $r->days;
            $production = round((float) $r->production, 2);

            return [
                'Provider Id' => $this->providerLabel($r->ProvNum, $providers),
                'Provider' => $providers[(int) $r->ProvNum]['name'] ?? '',
                'Working Days' => $days,
                'Production' => $production,
                'Avg Prod Per Day' => $days > 0 ? round($production / $days, 2) : 0.0,
                'Working Dates' => $this->sortedList($r->dates),
            ];
        }, $rows);
    }

    /** Production per patient visit — Jarvis: Patient, Production, Number Of Visit. */
    private function productionByPatient(MetricFilter $filter): array
    {
        $patDate = $this->patDate();
        $visitCodes = $this->inList(self::HYGIENE_VISIT_CODES);

        $rows = DB::select("
            SELECT pl.PatNum,
                   COALESCE(SUM(pl.ProcFee), 0) AS production,
                   COUNT(DISTINCT CASE WHEN pc.ProcCode IN ({$visitCodes}) THEN {$patDate} END) AS visits
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}
            GROUP BY pl.PatNum
            ORDER BY production DESC
        ", $this->bindings($filter));

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));

        return array_map(fn ($r) => [
            'Patient Id' => (int) $r->PatNum,
            'Patient' => $patients[(int) $r->PatNum] ?? '',
            'Production' => round((float) $r->production, 2),
            'Number Of Visit' => (int) $r->visits,
        ], $rows);
    }

    /** Number of visits per day — Jarvis lists one row per patient visit. */
    private function visits(MetricFilter $filter): array
    {
        $rows = DB::select("
            SELECT pl.ProcDate AS day, pl.PatNum,
                   {$this->groupConcat('DISTINCT pl.ProvNum')} AS prov_nums
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}
            GROUP BY pl.ProcDate, pl.PatNum
            ORDER BY pl.ProcDate, pl.PatNum
        ", $this->bindings($filter));

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));
        $providers = $this->providerNames($filter, $this->numbersIn($rows, 'prov_nums'));

        return array_map(fn ($r) => [
            'Date' => $r->day,
            'Number Of Patient Visits' => 1,
            'Patient Ids' => (int) $r->PatNum,
            'Patients' => $patients[(int) $r->PatNum] ?? '',
            'Providers' => $this->providerList($r->prov_nums, $providers),
        ], $rows);
    }

    /**
     * Hygiene / Perio Reappointment — one row per visit, with the next appointment date.
     * Jarvis: Date, Patient, Hyg Reappoint, Recall Date, Provider.
     *
     * @param  list<string>|null  $codes  null = every hygiene-flagged procedure (the card's rule)
     */
    private function reappointments(MetricFilter $filter, ?array $codes): array
    {
        $codeFilter = $codes === null ? '' : ' AND pc.ProcCode IN ('.$this->inList($codes).')';

        $rows = DB::select("
            SELECT a.AptNum, pl.ProcDate AS day, pl.PatNum, pl.ProvNum,
                   a.NextAptNum, nxt.AptDateTime AS next_apt
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = pl.office_id
            LEFT JOIN od_appointments nxt ON nxt.AptNum = a.NextAptNum AND nxt.office_id = a.office_id
            WHERE {$this->hygieneOnly($filter)}{$codeFilter}
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
            GROUP BY a.AptNum, pl.ProcDate, pl.PatNum, pl.ProvNum, a.NextAptNum, nxt.AptDateTime
            ORDER BY pl.ProcDate, pl.PatNum
        ", $this->bindings($filter));

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));
        $providers = $this->providerNames($filter, array_map(fn ($r) => (int) $r->ProvNum, $rows));

        return array_map(function ($r) use ($patients, $providers) {
            $hasNext = $r->NextAptNum !== null && (string) $r->NextAptNum !== '0';

            return [
                'Date' => $r->day,
                'Patient Id' => (int) $r->PatNum,
                'Patient' => $patients[(int) $r->PatNum] ?? '',
                'Hyg Reappoint' => $hasNext ? 'Yes' : 'No',
                'Recall Date' => $hasNext ? substr((string) str_replace('T', ' ', (string) $r->next_apt), 0, 10) : '',
                'Provider' => $providers[(int) $r->ProvNum]['name'] ?? '',
            ];
        }, $rows);
    }

    /**
     * Retention — active patients of the age group, showing whether they were seen
     * for a recall visit inside the trailing window the card measures.
     */
    private function retention(MetricFilter $filter, array $codes, int $months, bool $adults): array
    {
        $codeIn = $this->inList($codes);
        $age = $this->age('pt.Birthdate');
        $ageTest = $adults ? "{$age} >= 18" : "{$age} < 18";
        $since = $this->monthsAgo($months);
        $clinicPat = $this->clinicScope($filter, 'pt');

        $rows = DB::select("
            SELECT pt.PatNum, pt.LName, pt.FName, {$age} AS age,
                   MAX(pl.ProcDate) AS last_visit
            FROM od_patients pt
            LEFT JOIN od_procedure_logs pl
                   ON pl.PatNum = pt.PatNum AND pl.office_id = pt.office_id
                  AND pl.ProcStatus IN ({$this->completed()})
                  AND pl.ProcDate >= {$since}
                  AND pl.CodeNum IN (SELECT CodeNum FROM od_procedures WHERE office_id = pt.office_id AND ProcCode IN ({$codeIn}))
            WHERE pt.office_id = ? AND pt.PatStatus = 'Patient' AND {$ageTest}{$clinicPat}
            GROUP BY pt.PatNum, pt.LName, pt.FName, age
            ORDER BY last_visit DESC, pt.LName, pt.FName
        ", [$filter->officeId]);

        return array_map(fn ($r) => [
            'Patient Id' => (int) $r->PatNum,
            'Patient' => trim(($r->LName ?? '').', '.($r->FName ?? ''), ', '),
            'Age' => (int) $r->age,
            'Last Recall Visit' => $r->last_visit ?? '',
            'Retained' => $r->last_visit ? 'Yes' : 'No',
        ], $rows);
    }

    /**
     * One row per procedure. With $codes null this is every hygiene procedure, which is
     * what "Hygiene Production per Procedure" divides by.
     */
    private function procedures(MetricFilter $filter, ?array $codes): array
    {
        $codeFilter = $codes === null ? '' : ' AND pc.ProcCode IN ('.$this->inList($codes).')';

        $rows = DB::select("
            SELECT pl.ProcDate AS day, pl.PatNum, pl.ProvNum, pc.ProcCode, pc.Descript, pl.ProcFee
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            WHERE {$this->hygieneProduction($filter)}{$codeFilter}
            ORDER BY pl.ProcDate, pl.PatNum
        ", $this->bindings($filter));

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));
        $providers = $this->providerNames($filter, array_map(fn ($r) => (int) $r->ProvNum, $rows));

        return array_map(fn ($r) => [
            'Date' => $r->day,
            'Patient Id' => (int) $r->PatNum,
            'Patient' => $patients[(int) $r->PatNum] ?? '',
            'Code' => $r->ProcCode,
            'Description' => $r->Descript,
            'Production' => round((float) $r->ProcFee, 2),
            'Provider' => $providers[(int) $r->ProvNum]['name'] ?? '',
        ], $rows);
    }

    /** % of Hygiene Visits with TX Plan — one row per hygiene visit, flagged. */
    private function visitsWithTxPlan(MetricFilter $filter): array
    {
        $rows = DB::select("
            SELECT pl.ProcDate AS day, pl.PatNum, pl.ProvNum,
                   MAX(CASE WHEN tp.PatNum IS NULL THEN 0 ELSE 1 END) AS has_tx
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            LEFT JOIN (
                SELECT DISTINCT PatNum
                FROM od_procedure_logs
                WHERE office_id = ? AND ProcStatus IN ({$this->treatmentPlanned()})
                  AND ProcDate BETWEEN ? AND ?
            ) tp ON tp.PatNum = pl.PatNum
            WHERE {$this->hygieneOnly($filter)}
            GROUP BY pl.ProcDate, pl.PatNum, pl.ProvNum
            ORDER BY pl.ProcDate, pl.PatNum
        ", [$filter->officeId, $filter->start, $filter->end, ...$this->bindings($filter)]);

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));
        $providers = $this->providerNames($filter, array_map(fn ($r) => (int) $r->ProvNum, $rows));

        return array_map(fn ($r) => [
            'Date' => $r->day,
            'Patient Id' => (int) $r->PatNum,
            'Patient' => $patients[(int) $r->PatNum] ?? '',
            'Received Tx Plan' => ((int) $r->has_tx) === 1 ? 'Yes' : 'No',
            'Provider' => $providers[(int) $r->ProvNum]['name'] ?? '',
        ], $rows);
    }

    /** # of Tx plan per Day — plans presented per day (patient + DateTP, as the card counts them). */
    private function txPlansByDay(MetricFilter $filter): array
    {
        $clinic = $this->clinicScope($filter, 'pl');

        $rows = DB::select("
            SELECT pl.DateTP AS day,
                   COUNT(DISTINCT pl.PatNum) AS plans,
                   {$this->groupConcat('DISTINCT pl.PatNum')} AS pat_nums
            FROM od_procedure_logs pl
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->treatmentPlanned()})
              AND pl.DateTP IS NOT NULL
              AND pl.DateTP BETWEEN ? AND ?{$clinic}
            GROUP BY pl.DateTP
            ORDER BY pl.DateTP
        ", [$filter->officeId, $filter->start, $filter->end]);

        $patients = $this->patientNames($filter, $this->numbersIn($rows, 'pat_nums'));

        return array_map(fn ($r) => [
            'Date' => $r->day,
            'Tx Plans' => (int) $r->plans,
            'Patient Ids' => $this->sortedList($r->pat_nums),
            'Patients' => $this->namesFor($r->pat_nums, $patients),
        ], $rows);
    }

    /** Average Hygiene Production per Hour — production and scheduled minutes per day. */
    private function productionPerHourByDay(MetricFilter $filter): array
    {
        $rows = DB::select("
            SELECT pl.ProcDate AS day,
                   COALESCE(SUM(pl.ProcFee), 0) AS production,
                   COALESCE(SUM(LENGTH(a.Pattern) * 5), 0) AS minutes
            FROM od_procedure_logs pl
            {$this->codeJoin()}
            JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = pl.office_id
            WHERE {$this->hygieneOnly($filter)}
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
              AND a.Pattern IS NOT NULL AND a.Pattern != ''
            GROUP BY pl.ProcDate
            ORDER BY pl.ProcDate
        ", $this->bindings($filter));

        return array_map(function ($r) {
            $hours = round((float) $r->minutes / 60, 2);
            $production = round((float) $r->production, 2);

            return [
                'Date' => $r->day,
                'Production' => $production,
                'Scheduled Minutes' => (int) $r->minutes,
                'Hours' => $hours,
                'Prod Per Hour' => $hours > 0 ? round($production / $hours, 2) : 0.0,
            ];
        }, $rows);
    }

    /** Case Acceptance Rate — presented vs completed/scheduled hygiene treatment, per patient. */
    private function caseAcceptanceByPatient(MetricFilter $filter): array
    {
        $clinic = $this->clinicScope($filter, 'pl');
        $completed = $this->completed();
        $planned = $this->treatmentPlanned();

        $rows = DB::select("
            SELECT pl.PatNum,
                   COALESCE(SUM(CASE WHEN pl.ProcStatus IN ({$completed}) THEN pl.ProcFee ELSE 0 END), 0) AS completed,
                   COALESCE(SUM(CASE WHEN pl.ProcStatus IN ({$planned})   THEN pl.ProcFee ELSE 0 END), 0) AS planned
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = pl.office_id
            WHERE pl.office_id = ?
              AND pc.IsHygiene IN ('true', '1', 1)
              AND pl.ProcStatus IN ({$completed}, {$planned})
              AND pl.DateTP BETWEEN ? AND ?{$clinic}
            GROUP BY pl.PatNum
            ORDER BY planned DESC
        ", [$filter->officeId, $filter->start, $filter->end]);

        $patients = $this->patientNames($filter, array_map(fn ($r) => (int) $r->PatNum, $rows));

        return array_map(function ($r) use ($patients) {
            $completed = round((float) $r->completed, 2);
            $planned = round((float) $r->planned, 2);
            $presented = round($completed + $planned, 2);

            return [
                'Patient Id' => (int) $r->PatNum,
                'Patient' => $patients[(int) $r->PatNum] ?? '',
                'Presented' => $presented,
                'Completed' => $completed,
                'Not Accepted' => $planned,
                'Acceptance' => $presented > 0 ? round($completed / $presented * 100, 2).'%' : '0%',
            ];
        }, $rows);
    }

    // ── Shared SQL ───────────────────────────────────────────────────────────

    private function codeJoin(): string
    {
        return 'JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = pl.office_id';
    }

    /** The card's "hygiene production" predicate: IsHygiene OR an explicit hygiene code. */
    private function hygieneProduction(MetricFilter $filter): string
    {
        $codes = $this->inList(self::HYGIENE_PRODUCTION_CODES);

        return "pl.office_id = ?
              AND (pc.IsHygiene IN ('true', '1', 1) OR pc.ProcCode IN ({$codes}))
              AND pl.ProcStatus IN ({$this->completed()})
              AND pl.ProcDate BETWEEN ? AND ?".$this->clinicScope($filter, 'pl');
    }

    /** The stricter predicate the provider/appointment cards use: IsHygiene only. */
    private function hygieneOnly(MetricFilter $filter): string
    {
        return "pl.office_id = ?
              AND pc.IsHygiene IN ('true', '1', 1)
              AND pl.ProcStatus IN ({$this->completed()})
              AND pl.ProcDate BETWEEN ? AND ?".$this->clinicScope($filter, 'pl');
    }

    /** @return list<mixed> bindings for hygieneProduction()/hygieneOnly(), in order */
    private function bindings(MetricFilter $filter): array
    {
        return [$filter->officeId, $filter->start, $filter->end];
    }

    private function clinicScope(MetricFilter $filter, string $alias): string
    {
        if ($filter->clinics === []) {
            return '';
        }

        return " AND {$alias}.ClinicNum IN (".implode(',', array_map('intval', $filter->clinics)).')';
    }

    private function completed(): string
    {
        return ProcStatus::inList(ProcStatus::completed());
    }

    private function treatmentPlanned(): string
    {
        return ProcStatus::inList(ProcStatus::treatmentPlanned());
    }

    /** @param  list<string>  $codes */
    private function inList(array $codes): string
    {
        return "'".implode("','", array_map('addslashes', $codes))."'";
    }

    private function patDate(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "(pl.PatNum || '-' || pl.ProcDate)"
            : "CONCAT(pl.PatNum, '-', pl.ProcDate)";
    }

    private function age(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST((strftime('%Y', 'now') - strftime('%Y', {$column})) AS INT)"
            : "TIMESTAMPDIFF(YEAR, {$column}, CURDATE())";
    }

    private function monthsAgo(int $months): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "date('now', '-{$months} month')"
            : "DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)";
    }

    private function groupConcat(string $expression): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "GROUP_CONCAT({$expression})"
            : "GROUP_CONCAT({$expression} SEPARATOR ',')";
    }

    // ── Labels ───────────────────────────────────────────────────────────────

    /**
     * @param  list<int>  $provNums
     * @return array<int, array{abbr: string, name: string}>
     */
    private function providerNames(MetricFilter $filter, array $provNums): array
    {
        $provNums = array_values(array_unique(array_filter($provNums)));

        if ($provNums === []) {
            return [];
        }

        return DB::table('od_providers')
            ->where('office_id', $filter->officeId)
            ->whereIn('ProvNum', $provNums)
            ->get(['ProvNum', 'Abbr', 'LName', 'FName'])
            ->mapWithKeys(fn ($p) => [(int) $p->ProvNum => [
                'abbr' => (string) ($p->Abbr ?? ''),
                'name' => trim(((string) ($p->LName ?? '')).', '.((string) ($p->FName ?? '')), ', '),
            ]])
            ->all();
    }

    /**
     * @param  list<int>  $patNums
     * @return array<int, string>
     */
    private function patientNames(MetricFilter $filter, array $patNums): array
    {
        $patNums = array_values(array_unique(array_filter($patNums)));

        if ($patNums === []) {
            return [];
        }

        return DB::table('od_patients')
            ->where('office_id', $filter->officeId)
            ->whereIn('PatNum', $patNums)
            ->get(['PatNum', 'LName', 'FName'])
            ->mapWithKeys(fn ($p) => [
                (int) $p->PatNum => trim(((string) ($p->LName ?? '')).', '.((string) ($p->FName ?? '')), ', '),
            ])
            ->all();
    }

    /** "45 - HYG-Lori", as Jarvis labels a provider. */
    private function providerLabel($provNum, array $providers): string
    {
        $abbr = $providers[(int) $provNum]['abbr'] ?? '';

        return $abbr === '' ? (string) $provNum : $provNum.' - '.$abbr;
    }

    /**
     * Add the "Provider Ids" / "Providers" columns Jarvis puts on per-day rows.
     *
     * @param  list<object>  $rows
     * @param  callable(object): array<string, mixed>  $base
     * @return list<array<string, mixed>>
     */
    private function withProviderColumns(MetricFilter $filter, array $rows, callable $base): array
    {
        $providers = $this->providerNames($filter, $this->numbersIn($rows, 'prov_nums'));

        return array_map(fn ($r) => $base($r) + [
            'Provider Ids' => $this->providerIdList($r->prov_nums, $providers),
            'Providers' => $this->providerList($r->prov_nums, $providers),
        ], $rows);
    }

    /**
     * @param  list<object>  $rows
     * @return list<int>
     */
    private function numbersIn(array $rows, string $column): array
    {
        $numbers = [];

        foreach ($rows as $row) {
            foreach ($this->split($row->{$column} ?? null) as $value) {
                $numbers[] = (int) $value;
            }
        }

        return $numbers;
    }

    /** @return list<string> */
    private function split($concatenated): array
    {
        if ($concatenated === null || $concatenated === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $concatenated)), fn ($v) => $v !== ''));
    }

    private function sortedList($concatenated): string
    {
        $values = $this->split($concatenated);
        sort($values);

        return implode(', ', $values);
    }

    private function providerIdList($concatenated, array $providers): string
    {
        return implode(', ', array_map(fn ($n) => $this->providerLabel($n, $providers), $this->split($concatenated)));
    }

    private function providerList($concatenated, array $providers): string
    {
        $names = array_filter(array_map(fn ($n) => $providers[(int) $n]['name'] ?? '', $this->split($concatenated)));

        return implode(' | ', array_unique($names));
    }

    private function namesFor($concatenated, array $names): string
    {
        $found = array_filter(array_map(fn ($n) => $names[(int) $n] ?? '', $this->split($concatenated)));

        return implode(' | ', array_unique($found));
    }
}
