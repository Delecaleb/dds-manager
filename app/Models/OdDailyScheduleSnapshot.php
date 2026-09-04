<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OdDailyScheduleSnapshot extends Model
{
    use BelongsToOffice;

    protected $table = 'od_daily_schedule_snapshots';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'snapshot_date',
        'sched_production',
        'sched_pts_visit',
        'sched_new_pts_visit',
        'open_appt_hours',
        'unscheduled_tx',
        'is_locked',
        'snapshot_taken_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date:Y-m-d',
            'sched_production' => 'float',
            'sched_pts_visit' => 'integer',
            'sched_new_pts_visit' => 'integer',
            'open_appt_hours' => 'float',
            'unscheduled_tx' => 'float',
            'is_locked' => 'boolean',
            'snapshot_taken_at' => 'datetime',
        ];
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    public function scopeForPeriod(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('snapshot_date', [$start, $end]);
    }
}
