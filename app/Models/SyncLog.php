<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use BelongsToOffice;

    protected $fillable = [
        'office_id',
        'module',
        'last_primary_key',
        'last_synced_at',
        'total_processed',
        'retry_count',
        'status',
        'last_error',
        'started_at',
        'finished_at',
    ];
}
