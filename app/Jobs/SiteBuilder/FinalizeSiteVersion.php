<?php

namespace App\Jobs\SiteBuilder;

use App\Models\SiteBuildPage;
use App\Models\SiteBuildVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Last step of a run: ready when every page has content, partial when some failed,
 * failed when none did. A usable version becomes the site's current version.
 */
class FinalizeSiteVersion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public readonly int $versionId) {}

    public function handle(): void
    {
        $version = SiteBuildVersion::with('pages', 'build')->findOrFail($this->versionId);
        $withContent = $version->pages->filter(fn (SiteBuildPage $page) => $page->hasContent())->count();

        $status = match (true) {
            $withContent === $version->pages->count() => SiteBuildVersion::READY,
            $withContent > 0 => SiteBuildVersion::PARTIAL,
            default => SiteBuildVersion::FAILED,
        };

        $version->update([
            'status' => $status,
            'error' => $status === SiteBuildVersion::FAILED ? 'No page could be generated.' : null,
            'finished_at' => now(),
        ]);

        if ($version->isUsable()) {
            $version->build->update(['current_version_id' => $version->id]);
        }
    }
}
