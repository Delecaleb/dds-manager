<?php

namespace App\Jobs\SiteBuilder;

use App\Domain\SiteBuilder\SiteContentGenerator;
use App\Domain\SiteBuilder\SiteHtml;
use App\Models\SiteBuildVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

/**
 * Step 1 of a site run: the design system (stylesheet, header, footer, fonts). Every
 * page job builds on it, so a failure here fails the run (the chain's catch handler).
 */
class GenerateSiteDesign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public int $backoff = 60;

    public int $timeout;

    public function __construct(public readonly int $versionId)
    {
        $this->timeout = (int) config('site_builder.job_timeout');
    }

    public function handle(SiteContentGenerator $generator): void
    {
        $version = SiteBuildVersion::findOrFail($this->versionId);
        $version->update(['status' => SiteBuildVersion::DESIGNING, 'started_at' => $version->started_at ?? now()]);

        $design = $generator->design($version->brief, $this->logoForModel($version->brief['logo_path'] ?? null));
        $version->recordUsage($design['usage']);

        $version->update([
            'status' => SiteBuildVersion::BUILDING,
            'design' => [
                // Served as its own text/css file, so it needs no HTML cleaning.
                'css' => $design['css'],
                'header_html' => SiteHtml::clean($design['header_html']),
                'footer_html' => SiteHtml::clean($design['footer_html']),
                'google_fonts_url' => $design['google_fonts_url'],
            ],
        ]);
    }

    /**
     * The logo as an image the model can look at. Only raster formats the API accepts;
     * an SVG logo is still used on the site, the model just doesn't see it.
     */
    private function logoForModel(?string $path): ?array
    {
        $types = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
        $disk = Storage::disk(config('site_builder.disk'));

        if (! $path || ! isset($types[$extension]) || ! $disk->exists($path)) {
            return null;
        }

        return ['media_type' => $types[$extension], 'data' => base64_encode($disk->get($path))];
    }
}
