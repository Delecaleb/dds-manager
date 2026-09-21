<?php

namespace App\Domain\TreatmentAcceptance;

use App\Domain\Support\ProcStatus;
use App\Enums\AppointmentStatus;
use App\Enums\PatientStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Patient-level Tx Miner breakdown (the drill-down behind a By Month row).
 *
 * Definition, verified row-for-row against Jarvis's "Tx Miner Breakdown" export
 * (8 Mile, Jul 2026 — 102/102 patients, $ totals exact):
 *
 *  - Population: every TP or completed procedure whose DateTP (the date it was
 *    treatment-planned) falls in the period, grouped by patient. ProcDate is NOT
 *    used — a procedure planned in July and completed in August belongs to July.
 *  - Tx Scheduled / Unscheduled: fee of those procedures still TP, split on whether
 *    their appointment is still Scheduled (see TxScheduling — broken appointments
 *    keep AptNum, so AptNum alone is not enough).
 *  - Completed TX $: fee of those procedures now completed.
 *  - Type: "New" when the patient's DateFirstVisit falls in the period.
 *  - Preferred Provider: the distinct ProvNums on those procedures.
 *  - Date Planned: the distinct DateTPs; Date Created: SecDateEntry of the treatment
 *    plans those procedures are attached to.
 *  - Next Visit: first Scheduled appointment on/after $asOf (today), not the period.
 *
 * Insurance is approximated from the patient's latest appointment InsPlan1 because
 * patplan/inssub are not synced. Remaining Benefits and referrals are not computed
 * (benefit, claimproc-by-benefit-year and refattach are not synced).
 */
class TxMinerPatientBreakdownService
{
    /**
     * @param  Builder  $procedureScope  od_procedure_logs (aliased `pl`) already narrowed to the
     *                                   Tx Miner filters (locations, providers, codes, LOB). It must
     *                                   NOT carry a date filter — the period is applied here.
     * @return TxMinerPatientRow[] sorted by patient name, case-insensitive
     */
    public function patients(Builder $procedureScope, string $start, string $end, CarbonInterface $asOf): array
    {
        $procs = TxScheduling::joinScheduledAppointment(clone $procedureScope)
            ->whereBetween('pl.DateTP', [$start, $end])
            ->whereIn('pl.ProcStatus', [...ProcStatus::treatmentPlanned(), ...ProcStatus::completed()])
            ->select(['pl.office_id', 'pl.ProcNum', 'pl.PatNum', 'pl.ProvNum', 'pl.ProcFee', 'pl.ProcStatus', 'pl.DateTP', TxScheduling::scheduledAptColumn()])
            ->get();

        $rows = [];

        foreach ($procs->groupBy('office_id') as $officeId => $officeProcs) {
            array_push($rows, ...$this->officeRows((int) $officeId, $officeProcs, $start, $end, $asOf));
        }

        usort($rows, fn (TxMinerPatientRow $a, TxMinerPatientRow $b) => strcasecmp($a->name, $b->name));

        return $rows;
    }

    /** @return TxMinerPatientRow[] */
    private function officeRows(int $officeId, Collection $procs, string $start, string $end, CarbonInterface $asOf): array
    {
        $patNums = $procs->pluck('PatNum')->unique()->values()->all();

        $patients = DB::table('od_patients')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $patNums)
            ->get(['PatNum', 'LName', 'FName', 'ChartNumber', 'HmPhone', 'WirelessPhone', 'Email', 'DateFirstVisit', 'PatStatus'])
            ->keyBy('PatNum');

        $providers = DB::table('od_providers')
            ->where('office_id', $officeId)
            ->whereIn('ProvNum', $procs->pluck('ProvNum')->unique()->values()->all())
            ->get(['ProvNum', 'LName', 'FName', 'Abbr'])
            ->keyBy('ProvNum');

        $nextVisits = $this->nextVisits($officeId, $patNums, $asOf);
        $insurance = $this->insuranceCarriers($officeId, $patNums);
        $datesCreated = $this->treatmentPlanCreatedDates($officeId, $procs->pluck('ProcNum')->all());

        $rows = [];

        foreach ($procs->groupBy('PatNum') as $patNum => $patProcs) {
            $patient = $patients->get($patNum);
            $scheduled = 0.0;
            $unscheduled = 0.0;
            $completed = 0.0;

            foreach ($patProcs as $proc) {
                $fee = (float) $proc->ProcFee;

                if (in_array((string) $proc->ProcStatus, ProcStatus::completed(), true)) {
                    $completed += $fee;
                } elseif (TxScheduling::isScheduled($proc)) {
                    $scheduled += $fee;
                } else {
                    $unscheduled += $fee;
                }
            }

            $rows[] = new TxMinerPatientRow(
                officeId: $officeId,
                patNum: (int) $patNum,
                name: $patient ? ($patient->LName ?? '').', '.($patient->FName ?? '') : 'Patient '.$patNum,
                chartNumber: (string) ($patient->ChartNumber ?? ''),
                homePhone: (string) ($patient->HmPhone ?? ''),
                wirelessPhone: (string) ($patient->WirelessPhone ?? ''),
                email: (string) ($patient->Email ?? ''),
                isNew: $patient !== null && $patient->DateFirstVisit >= $start && $patient->DateFirstVisit <= $end,
                txScheduled: round($scheduled, 2),
                txUnscheduled: round($unscheduled, 2),
                completedTx: round($completed, 2),
                nextVisit: $nextVisits[$patNum]['visit'] ?? null,
                nextHygieneVisit: $nextVisits[$patNum]['hygiene'] ?? null,
                status: $patient ? (PatientStatus::tryFrom((int) $patient->PatStatus)?->text() ?? '') : '',
                providers: $this->providersFor($patProcs, $providers),
                insurance: $insurance[$patNum] ?? null,
                datesPlanned: $patProcs->pluck('DateTP')->map(fn ($d) => substr((string) $d, 0, 10))->unique()->sort()->values()->all(),
                datesCreated: $datesCreated[$patNum] ?? [],
            );
        }

        return $rows;
    }

    /** @return array<int, array{num: int, name: string, abbr: string}> */
    private function providersFor(Collection $patProcs, Collection $providers): array
    {
        return $patProcs->pluck('ProvNum')
            ->map(fn ($n) => (int) $n)
            ->filter()
            ->unique()
            ->sort()
            ->map(function (int $provNum) use ($providers) {
                $p = $providers->get($provNum) ?? $providers->get((string) $provNum);
                $name = $p ? implode(', ', array_filter([trim((string) $p->LName), trim((string) ($p->FName ?? ''))])) : '';

                return [
                    'num' => $provNum,
                    'name' => $name !== '' ? $name : 'Provider '.$provNum,
                    'abbr' => (string) ($p->Abbr ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<string, array{visit: ?string, hygiene: ?string}> keyed by PatNum */
    private function nextVisits(int $officeId, array $patNums, CarbonInterface $asOf): array
    {
        return DB::table('od_appointments')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $patNums)
            ->where('AptStatus', AppointmentStatus::Scheduled->value)
            ->where('AptDateTime', '>=', $asOf->copy()->startOfDay()->toDateTimeString())
            ->groupBy('PatNum')
            ->selectRaw("PatNum, MIN(AptDateTime) AS next_visit, MIN(CASE WHEN IsHygiene IN ('1', 'true') THEN AptDateTime END) AS next_hygiene")
            ->get()
            ->mapWithKeys(fn ($r) => [$r->PatNum => [
                'visit' => $r->next_visit ? substr((string) $r->next_visit, 0, 10) : null,
                'hygiene' => $r->next_hygiene ? substr((string) $r->next_hygiene, 0, 10) : null,
            ]])
            ->all();
    }

    /**
     * Carrier on the patient's most recent appointment that has a primary plan.
     *
     * @return array<string, string> CarrierName keyed by PatNum
     */
    private function insuranceCarriers(int $officeId, array $patNums): array
    {
        $latest = DB::table('od_appointments')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $patNums)
            ->whereNotNull('InsPlan1')
            ->where('InsPlan1', '<>', '0')
            ->groupBy('PatNum')
            ->selectRaw('PatNum, MAX(AptDateTime) AS last_apt');

        return DB::table('od_appointments as a')
            ->joinSub($latest, 'l', fn ($j) => $j->on('a.PatNum', '=', 'l.PatNum')->on('a.AptDateTime', '=', 'l.last_apt'))
            ->join('od_insplans as ip', fn ($j) => $j->on('ip.PlanNum', '=', 'a.InsPlan1')->where('ip.office_id', '=', $officeId))
            ->join('od_carriers as c', fn ($j) => $j->on('c.CarrierNum', '=', 'ip.CarrierNum')->where('c.office_id', '=', $officeId))
            ->where('a.office_id', $officeId)
            ->where('a.InsPlan1', '<>', '0')
            ->pluck('c.CarrierName', 'a.PatNum')
            ->all();
    }

    /** @return array<string, string[]> treatment-plan SecDateEntry dates keyed by PatNum */
    private function treatmentPlanCreatedDates(int $officeId, array $procNums): array
    {
        return DB::table('od_treatment_plan_attachments as tpa')
            ->join('treatment_plans as tp', fn ($j) => $j->on('tp.TreatPlanNum', '=', 'tpa.TreatPlanNum')->on('tp.office_id', '=', 'tpa.office_id'))
            ->where('tpa.office_id', $officeId)
            ->whereIn('tpa.ProcNum', $procNums)
            ->whereNotNull('tp.SecDateEntry')
            ->distinct()
            ->get(['tp.PatNum', 'tp.SecDateEntry'])
            ->groupBy('PatNum')
            ->map(fn (Collection $g) => $g->pluck('SecDateEntry')->map(fn ($d) => substr((string) $d, 0, 10))->unique()->sort()->values()->all())
            ->all();
    }
}
