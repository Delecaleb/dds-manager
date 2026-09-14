<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderGoal extends Model
{
    protected $table = 'provider_goals';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'prov_num',
        'provider_name',
        'provider_type',
        'year_month',
        'goal_type',
        'recurring',
        'production_goal',
    ];

    protected function casts(): array
    {
        return [
            'prov_num' => 'integer',
            'recurring' => 'boolean',
            'production_goal' => 'float',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
