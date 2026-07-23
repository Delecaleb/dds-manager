<?php

namespace App\Domain\Recall;

use App\Domain\Support\MetricFilter;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for recall (recare) metrics — patients due/overdue/scheduled for
 * their next hygiene visit.
 *
 * od_recalls has no ClinicNum, so clinic scoping is applied via the patient's ClinicNum when
 * MetricFilter->clinics is set (multi-office aware).
 */
class RecallService
{
    /** Active recalls whose due date falls in the period. */
    public function due(MetricFilter $filter): int
    {
        return (int) $this->activeRecalls($filter)
            ->whereBetween('r.DateDue', [$filter->start, $filter->end])
            ->distinct()
            ->count('r.PatNum');
    }

    /** Active recalls past due (due date before today) as of now. */
    public function overdue(MetricFilter $filter): int
    {
        return (int) $this->activeRecalls($filter)
            ->whereDate('r.DateDue', '<', Carbon::today()->toDateString())
            ->distinct()
            ->count('r.PatNum');
    }

    /**
     * Patients due in the period who have a scheduled appointment on the books — i.e. the
     * recall was acted on. Counts distinct patients.
     */
    public function scheduled(MetricFilter $filter): int
    {
        return (int) $this->activeRecalls($filter)
            ->whereBetween('r.DateDue', [$filter->start, $filter->end])
            ->whereExists(function ($q) {
                $q->from('od_appointments as a')
                    ->whereColumn('a.PatNum', 'r.PatNum')
                    ->where('a.AptStatus', AppointmentStatus::Scheduled->value);
            })
            ->distinct()
            ->count('r.PatNum');
    }

    /** Due count broken down by recall type. @return array<int,int> RecallTypeNum => count */
    public function byType(MetricFilter $filter): array
    {
        return $this->activeRecalls($filter)
            ->whereBetween('r.DateDue', [$filter->start, $filter->end])
            ->selectRaw('r.RecallTypeNum, COUNT(DISTINCT r.PatNum) AS cnt')
            ->groupBy('r.RecallTypeNum')
            ->pluck('cnt', 'r.RecallTypeNum')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function summary(MetricFilter $filter): RecallSummary
    {
        $due = $this->due($filter);
        $scheduled = $this->scheduled($filter);

        return new RecallSummary(
            due: $due,
            overdue: $this->overdue($filter),
            scheduled: $scheduled,
            scheduledRate: $due > 0 ? round($scheduled / $due * 100, 2) : 0.0,
        );
    }

    /** Active (not disabled) recalls, scoped to the filter's clinics via the patient. */
    protected function activeRecalls(MetricFilter $filter): Builder
    {
        $q = DB::table('od_recalls as r')
            ->whereIn('r.IsDisabled', ['false', '0', 0]);

        if ($filter->clinics) {
            $q->join('od_patients as p', 'p.PatNum', '=', 'r.PatNum')
                ->whereIn('p.ClinicNum', $filter->clinics);
        }

        return $q;
    }
}
