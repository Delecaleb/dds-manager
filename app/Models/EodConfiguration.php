<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EodConfiguration extends Model
{
    protected $table = 'eod_configurations';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'metric_key',
        'subtab',
        'title',
        'description',
        'is_enabled',
        'is_locked',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_locked' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
