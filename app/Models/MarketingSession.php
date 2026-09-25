<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One visit: the events a visitor produced before going idle for the session timeout. */
class MarketingSession extends Model
{
    /** A visit ends after this many minutes without an event. */
    public const TIMEOUT_MINUTES = 30;

    protected $fillable = [
        'site_id', 'visitor_id', 'session_uid', 'started_at', 'last_event_at',
        'source', 'medium', 'campaign', 'term', 'content', 'referrer', 'landing_path',
        'click_id', 'click_source', 'device', 'browser', 'pageviews', 'has_signup',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_event_at' => 'datetime',
            'has_signup' => 'boolean',
            'pageviews' => 'integer',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(MarketingSite::class, 'site_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(MarketingVisitor::class, 'visitor_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'session_id');
    }
}
