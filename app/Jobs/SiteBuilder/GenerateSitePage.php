<?php

namespace App\Jobs\SiteBuilder;

use App\Domain\SiteBuilder\SiteContentGenerator;
use App\Domain\SiteBuilder\SiteGenerationException;
use App\Domain\SiteBuilder\SiteHtml;
use App\Models\SiteBuildPage;
use App\Models\SiteBuildVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * One page of a site run. A page that still fails on its last attempt is marked failed
 * instead of failing the job, so the rest of the site keeps generating; the version then
 * finishes as "partial" and the page can be regenerated on its own.
 */
class GenerateSitePage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public int $backoff = 60;

    public int $timeout;

    public function __construct(public readonly int $versionId, public readonly string $path)
    {
        $this->timeout = (int) config('site_builder.job_timeout');
    }

    public function handle(SiteContentGenerator $generator): void
    {
        $version = SiteBuildVersion::findOrFail($this->versionId);
        $page = $version->pages()->where('path', $this->path)->firstOrFail();
        $page->update(['status' => SiteBuildPage::GENERATING, 'error' => null]);

        $brief = $version->brief;
        $definition = collect($brief['pages'])->firstWhere('path', $this->path) ?? ['title' => $page->title, 'path' => $page->path];
        $notes = $version->scope === 'page' ? $version->revision_notes : null;

        try {
            $result = $generator->page($brief, $version->design ?? [], $definition, $notes);
        } catch (SiteGenerationException $e) {
            // The model answered but the content is unusable (declined, cut off, bad JSON):
            // the same request is unlikely to do better, so don't spend a retry on it.
            $this->markFailed($page, $e);

            return;
        } catch (Throwable $e) {
            // Transient API trouble (network, 429, 5xx) gets the job's retry.
            if ($this->attempts() < $this->tries) {
                $page->update(['status' => SiteBuildPage::PENDING]);

                throw $e;
            }
            $this->markFailed($page, $e);

            return;
        }

        $version->recordUsage($result['usage']);
        $page->update([
            'status' => SiteBuildPage::READY,
            'meta' => [
                'meta_title' => Str::limit(strip_tags($result['meta_title']), 70, ''),
                'meta_description' => Str::limit(strip_tags($result['meta_description']), 170, ''),
            ],
            'body_html' => SiteHtml::clean($result['body_html']),
        ]);
    }

    private function markFailed(SiteBuildPage $page, Throwable $e): void
    {
        Log::warning('Site builder page failed', ['version' => $this->versionId, 'path' => $this->path, 'error' => $e->getMessage()]);
        $page->update(['status' => SiteBuildPage::FAILED, 'error' => Str::limit($e->getMessage(), 1000)]);
    }
}
