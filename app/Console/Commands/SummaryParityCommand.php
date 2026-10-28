<?php

namespace App\Console\Commands;

use App\Domain\Patient\PatientActivityService;
use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Support\MetricFilter;
use App\Models\Office;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Read-only: compares every summary-backed figure with the raw-table reference
 * for each office, a set of periods, and each clinic of multi-clinic offices.
 * A summary may only serve a metric while this reports no differences.
 */
class SummaryParityCommand extends Command
{
    protected $signature = 'summaries:parity {offices?* : Office ids; default all}';

    protected $description = 'Read-only: check summary-table results match the raw-table queries';

    public function handle(PatientActivityService $activity, PatientVisitService $visits): int
    {
        // Compare what each path computes now, never a cached result.
        config(['metric_cache.enabled' => false]);

        $officeIds = $this->argument('offices') ?: Office::query()->orderBy('id')->pluck('id')->all();
        $today = now()->toDateString();
        $periods = [
            'this month' => [now()->startOfMonth()->toDateString(), $today],
            'last month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'YTD' => [now()->startOfYear()->toDateString(), $today],
            'last year' => [now()->subYear()->startOfYear()->toDateString(), now()->subYear()->endOfYear()->toDateString()],
            'one day' => [now()->subDays(7)->toDateString(), now()->subDays(7)->toDateString()],
        ];

        $checks = 0;
        $failures = 0;

        foreach ($officeIds as $officeId) {
            $officeId = (int) $officeId;
            $clinicSets = [[]];

            foreach (DB::table('patient_visit_days')->where('office_id', $officeId)->distinct()->pluck('clinic_num') as $clinic) {
                $clinicSets[] = [(int) $clinic];
            }

            if (count($clinicSets) === 2) {
                // Single-clinic office: the clinic filter equals the whole office.
                $clinicSets = [[]];
            }

            foreach ($periods as $label => [$start, $end]) {
                foreach ($clinicSets as $clinics) {
                    $filter = new MetricFilter($start, $end, $clinics, [], null, $officeId);
                    $raw = $activity->fromRaw($filter)->toArray() + $this->visitFigures($visits, $filter, raw: true);
                    $summary = $activity->fromSummary($filter)->toArray() + $this->visitFigures($visits, $filter, raw: false);
                    $checks++;

                    $diff = array_filter($raw, fn ($value, $key) => $summary[$key] !== $value, ARRAY_FILTER_USE_BOTH);
                    $scope = $clinics === [] ? 'all clinics' : 'clinic '.$clinics[0];

                    if ($diff === []) {
                        $this->line("  ok    office {$officeId} {$label} ({$scope})");

                        continue;
                    }

                    $failures++;
                    foreach ($diff as $key => $value) {
                        $this->error("  DIFF  office {$officeId} {$label} ({$scope}) {$key}: raw=".mb_strimwidth((string) $value, 0, 120, '…').' summary='.mb_strimwidth((string) $summary[$key], 0, 120, '…'));
                    }
                }
            }
        }

        $this->newLine();
        $failures === 0
            ? $this->info("Parity: {$checks} checks, all match.")
            : $this->error("Parity: {$failures} of {$checks} checks differ.");

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * PatientVisitService figures, from the summary or (raw: true) the raw tables. Lists are
     * compared as JSON so a single differing patient, date or amount shows up.
     *
     * @return array<string, int|string>
     */
    private function visitFigures(PatientVisitService $visits, MetricFilter $f, bool $raw): array
    {
        $compute = fn (PatientVisitService $s) => [
            'patientVisits' => $s->patientVisits($f),
            'newPatientVisits' => json_encode($s->newPatientVisits($f->start, $f->end, $f->clinics, [], $f->officeId)),
            'dailyStats' => json_encode($s->dailyStats($f->start, $f->end, $f->clinics, [], $f->officeId)),
            'visitsPerLocation' => $f->clinics === [] ? json_encode($s->visitsPerLocation($f->start, $f->end, [], $f->officeId)) : '',
        ];

        $figures = $raw ? $visits->onRawTables($compute) : $compute($visits);

        // The first-visit cohort (both helper forms) — per patient, so md5 keeps it short.
        $patients = app(PatientService::class);
        $cohort = function (PatientService $p) use ($f) {
            $clinic = $f->clinics ?: null;
            $builder = $p->firstVisitCohort($f->officeId, $clinic)->get()->map(fn ($r) => (int) $r->PatNum.'|'.$r->first_date)->sort()->values();
            $sql = collect(DB::select($p->firstVisitCohortSql('first_date', $f->officeId, $clinic)))->map(fn ($r) => (int) $r->PatNum.'|'.$r->first_date)->sort()->values();

            return ['firstVisitCohort' => md5($builder->implode(',')).' ('.$builder->count().')', 'firstVisitCohortSql' => md5($sql->implode(',')).' ('.$sql->count().')'];
        };

        return $figures + ($raw ? $patients->onRawTables($cohort) : $cohort($patients));
    }
}
