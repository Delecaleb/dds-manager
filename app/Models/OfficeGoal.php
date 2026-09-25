<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeGoal extends Model
{
    protected $table = 'office_goals';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'year_month',
        'goal_type',
        'gross_production',
        'net_production',
        'collection',
        'pts_visits',
        'npt_visits',
        'ini_bonding',
        'hyg_visits',
    ];

    protected function casts(): array
    {
        return [
            'gross_production' => 'float',
            'net_production' => 'float',
            'collection' => 'float',
            'pts_visits' => 'integer',
            'npt_visits' => 'integer',
            'ini_bonding' => 'integer',
            'hyg_visits' => 'integer',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
