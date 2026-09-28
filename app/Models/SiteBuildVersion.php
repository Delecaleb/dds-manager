<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One generation run of a site. `scope` is "site" (everything regenerated) or "page"
 * (one page regenerated; the design and other pages are copied from the previous run).
 */
class SiteBuildVersion extends Model
{
    public const QUEUED = 'queued';

    public const DESIGNING = 'designing';

    public const BUILDING = 'building';

    public const READY = 'ready';

    public const PARTIAL = 'partial';

    public const FAILED = 'failed';

    /** Statuses of a run still in progress. */
    public const ACTIVE = [self::QUEUED, self::DESIGNING, self::BUILDING];

    /** Statuses whose files can be previewed and downloaded. */
    public const USABLE = [self::READY, self::PARTIAL];

    protected $fillable = [
        'site_build_id', 'number', 'status', 'scope', 'scope_path', 'revision_notes', 'brief',
        'design', 'error', 'input_tokens', 'output_tokens', 'cache_read_tokens',
        'started_at', 'finished_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'brief' => 'array',
            'design' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(SiteBuild::class, 'site_build_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(SiteBuildPage::class)->orderBy('id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE, true);
    }

    public function isUsable(): bool
    {
        return in_array($this->status, self::USABLE, true);
    }

    /** Add one API call's token usage (the jobs call this after every request). */
    public function recordUsage(array $usage): void
    {
        $this->increment('input_tokens', (int) ($usage['input'] ?? 0));
        $this->increment('output_tokens', (int) ($usage['output'] ?? 0));
        $this->increment('cache_read_tokens', (int) ($usage['cache_read'] ?? 0));
    }
}
