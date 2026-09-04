<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdAppointmentScheduleSnapshot extends Model
{
    use BelongsToOffice;

    protected $table = 'od_appointment_schedule_snapshots';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'snapshot_date',
        'apt_num',
        'pat_num',
        'prov_num',
        'apt_date_time',
        'apt_status',
        'pattern',
        'is_new_patient',
        'proc_descript',
        'sched_production',
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
            'apt_date_time' => 'datetime',
            'apt_status' => 'integer',
            'is_new_patient' => 'boolean',
            'sched_production' => 'float',
            'unscheduled_tx' => 'float',
            'is_locked' => 'boolean',
            'snapshot_taken_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(OdPatient::class, 'pat_num', 'PatNum');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(OdProvider::class, 'prov_num', 'ProvNum');
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
