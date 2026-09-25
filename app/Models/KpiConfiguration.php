<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiConfiguration extends Model
{
    protected $table = 'kpi_configurations';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'kpi_key',
        'category',
        'transaction_type',
        'kpi_type',
        'line_of_business',
        'display_type',
        'name',
        'description',
        'is_enabled',
        'target_goal',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'target_goal' => 'float',
            'metadata' => 'array',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
