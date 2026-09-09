<?php

namespace App\Domain\Provider;

use App\Domain\Patient\PatientService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\TreatmentAcceptance\TreatmentAcceptanceService;
use Illuminate\Support\Facades\DB;

/**
 * Per-provider composer. Owns NO metric formulas — it iterates providers and calls the
 * domain services with `$filter->withProviders([$provNum])`, so per-provider numbers can
 * never drift from practice-wide numbers (same definitions, narrower scope).
 */
class ProviderService
{
    public function __construct(
        private readonly ProductionService $production,
        private readonly TreatmentAcceptanceService $treatmentAcceptance,
        private readonly PatientService $patients,
    ) {}

    /**
     * One row per active provider: production summary + case acceptance + new patients,
     * all scoped to that provider via the shared services.
     *
     * @return array<int,array<string,mixed>>
     */
    public function scorecard(MetricFilter $filter): array
    {
        $rows = [];
        foreach ($this->activeProviders($filter) as $prov) {
            $scoped = $filter->withProviders([(int) $prov->ProvNum]);
            $prod = $this->production->summary($scoped);

            $rows[] = [
                'provider_id' => (int) $prov->ProvNum,
                'provider' => trim(($prov->Abbr ?: $prov->LName).' '.$prov->PName),
                'gross_production' => $prod->gross,
                'net_production' => $prod->net,
                'collection' => $prod->collection,
                'patient_visits' => $prod->patientVisits,
                'production_per_visit' => $prod->productionPerVisit,
                'production_per_day' => $prod->productionPerDay,
                'new_patients' => $this->patients->newPatientCount($scoped),
                'case_acceptance' => $this->treatmentAcceptance->rate($scoped),
            ];
        }

        usort($rows, fn ($a, $b) => $b['net_production'] <=> $a['net_production']);

        return $rows;
    }

    /** Providers with production in the period (respects the filter's clinics). */
    private function activeProviders(MetricFilter $filter)
    {
        $q = DB::table('od_providers as pr')
            ->join('od_procedure_logs as pl', 'pl.ProvNum', '=', 'pr.ProvNum')
            ->whereIn('pr.IsHidden', ['false', '0', 0])
            ->whereBetween('pl.ProcDate', [$filter->start, $filter->end]);
        if ($filter->clinics) {
            $q->whereIn('pl.ClinicNum', $filter->clinics);
        }

        return $q->select('pr.ProvNum', 'pr.Abbr', 'pr.LName', 'pr.PName')
            ->distinct()
            ->get();
    }
}
