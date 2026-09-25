<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A single tracked action: a page view, a signup, or a custom event the site sends. */
class MarketingEvent extends Model
{
    public const TYPE_PAGEVIEW = 'pageview';

    public const TYPE_SIGNUP = 'signup';

    protected $fillable = [
        'site_id', 'visitor_id', 'session_id', 'type',
        'path', 'title', 'referrer', 'payload', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
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

    public function session(): BelongsTo
    {
        return $this->belongsTo(MarketingSession::class, 'session_id');
    }
}
