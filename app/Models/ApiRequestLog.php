<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One Developer API request: who asked, for what, and how much came back. */
class ApiRequestLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['api_key_id', 'method', 'path', 'query', 'status', 'rows', 'ip', 'duration_ms'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
