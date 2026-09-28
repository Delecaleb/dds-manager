<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One generated page of a site version: its <main> content and SEO meta. */
class SiteBuildPage extends Model
{
    public const PENDING = 'pending';

    public const GENERATING = 'generating';

    public const READY = 'ready';

    public const COPIED = 'copied';

    public const FAILED = 'failed';

    protected $fillable = ['site_build_version_id', 'path', 'title', 'status', 'meta', 'body_html', 'error'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(SiteBuildVersion::class, 'site_build_version_id');
    }

    /** Has content that can be published (freshly generated or carried over). */
    public function hasContent(): bool
    {
        return in_array($this->status, [self::READY, self::COPIED], true) && $this->body_html !== null;
    }
}
