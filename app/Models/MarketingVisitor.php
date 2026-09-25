<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One browser on one site. Anonymous until a signup gives contact details, at which point
 * every session already recorded against it becomes part of that person's journey.
 */
class MarketingVisitor extends Model
{
    protected $fillable = [
        'site_id', 'visitor_uid', 'first_seen_at', 'last_seen_at',
        'first_source', 'first_medium', 'first_campaign', 'first_term', 'first_content',
        'first_referrer', 'first_landing_path', 'first_click_id', 'first_click_source',
        'identified_at', 'email', 'phone', 'name',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'identified_at' => 'datetime',
        ];
    }

    public function isIdentified(): bool
    {
        return $this->identified_at !== null;
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(MarketingSite::class, 'site_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MarketingSession::class, 'visitor_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'visitor_id');
    }
}
