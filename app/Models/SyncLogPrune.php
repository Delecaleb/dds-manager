<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLogPrune extends Model
{
    use BelongsToOffice;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sync_log_prune';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'office_id',
        'table_name',
        'mode',
        'range',
        'local_count',
        'remote_count',
        'orphan_count',
        'status',
        'started_at',
        'completed_at',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'office_id' => 'integer',
            'local_count' => 'integer',
            'remote_count' => 'integer',
            'orphan_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Office associated with this prune log.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
