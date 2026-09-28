<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An AI Website Builder project: the brief a site is generated from. Each generation
 * run is a SiteBuildVersion that snapshots the brief, so history stays accurate after
 * the brief is edited.
 */
class SiteBuild extends Model
{
    protected $fillable = [
        'name', 'domain', 'marketing_site_id', 'business', 'brand', 'logo_path',
        'pages', 'seo', 'instructions', 'current_version_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'business' => 'array',
            'brand' => 'array',
            'pages' => 'array',
            'seo' => 'array',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SiteBuildVersion::class)->orderByDesc('number');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(SiteBuildVersion::class, 'current_version_id');
    }

    public function marketingSite(): BelongsTo
    {
        return $this->belongsTo(MarketingSite::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The version generating right now, if any (one run at a time per site). */
    public function activeVersion(): ?SiteBuildVersion
    {
        return $this->versions()->whereIn('status', SiteBuildVersion::ACTIVE)->first();
    }

    /** Everything a generation run needs, frozen onto the version. */
    public function brief(): array
    {
        return [
            'name' => $this->name,
            'domain' => $this->domain,
            'business' => $this->business,
            'brand' => $this->brand,
            'logo_path' => $this->logo_path,
            'pages' => $this->pages,
            'seo' => $this->seo,
            'instructions' => $this->instructions,
            'tracking_site_key' => $this->marketingSite?->site_key,
        ];
    }
}
