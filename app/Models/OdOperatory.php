<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * OpenDental `operatory`: a chair/room appointments are booked into (od_appointments.Op).
 * Synced by OperatorySyncService.
 */
class OdOperatory extends Model
{
    use BelongsToOffice;

    protected $table = 'od_operatories';

    protected $primaryKey = 'OperatoryNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'OperatoryNum',
        'OpName',
        'Abbrev',
        'ItemOrder',
        'IsHidden',
        'ProvDentist',
        'ProvHygienist',
        'IsHygiene',
        'ClinicNum',
        'DateTStamp',
        'SetProspective',
        'IsWebSched',
        'IsNewPatAppt',
        'OperatoryType',
    ];

    protected function casts(): array
    {
        return [
            'office_id' => 'integer',
            'OperatoryNum' => 'integer',
            'ItemOrder' => 'integer',
            'IsHidden' => 'boolean',
            'IsHygiene' => 'boolean',
            'ClinicNum' => 'integer',
            'ProvDentist' => 'integer',
            'ProvHygienist' => 'integer',
        ];
    }

    /** Shown on the schedule (OpenDental hides retired chairs rather than deleting them). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('IsHidden', false);
    }

    /** The column label: the operatory's name, else its abbreviation, else its number. */
    public function label(): string
    {
        return $this->OpName ?: ($this->Abbrev ?: 'Op '.$this->OperatoryNum);
    }
}
