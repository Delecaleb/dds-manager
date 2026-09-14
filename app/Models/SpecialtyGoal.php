<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialtyGoal extends Model
{
    protected $table = 'specialty_goals';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'year_month',
        'goal_type',
        'doctor',
        'hygiene',
        'oral_surgery',
        'clear_aligners',
        'perio',
        'pedo',
        'endo',
        'ortho',
        'prostho',
    ];

    protected function casts(): array
    {
        return [
            'doctor' => 'float',
            'hygiene' => 'float',
            'oral_surgery' => 'float',
            'clear_aligners' => 'float',
            'perio' => 'float',
            'pedo' => 'float',
            'endo' => 'float',
            'ortho' => 'float',
            'prostho' => 'float',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
