<?php

namespace App\Domain\SiteBuilder;

use App\Jobs\SiteBuilder\FinalizeSiteVersion;
use App\Jobs\SiteBuilder\GenerateSiteDesign;
use App\Jobs\SiteBuilder\GenerateSitePage;
use App\Models\SiteBuild;
use App\Models\SiteBuildPage;
use App\Models\SiteBuildVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * The AI Website Builder's operations: save a brief, start a generation run (whole site
 * or one page), and package a finished version as a zip. Generation itself happens in
 * queued jobs — design first, then one job per page, then a finalize step.
 */
final class SiteBuilderService
{
    public function __construct(private readonly SiteAssembler $assembler) {}

    /** @param array $brief validated brief fields (StoreSiteBuildRequest::brief()) */
    public function create(array $brief, ?UploadedFile $logo, User $user): SiteBuild
    {
        $build = SiteBuild::create($brief + ['created_by' => $user->id]);
        $this->storeLogo($build, $logo);

        return $build;
    }

    public function update(SiteBuild $build, array $brief, ?UploadedFile $logo, bool $removeLogo = false): SiteBuild
    {
        $build->update($brief);
        if ($removeLogo && $build->logo_path) {
            Storage::disk(config('site_builder.disk'))->delete($build->logo_path);
            $build->update(['logo_path' => null]);
        }
        $this->storeLogo($build, $logo);

        return $build;
    }

    /** Generate every page from the current brief. */
    public function generateSite(SiteBuild $build, User $user, ?string $revisionNotes = null): SiteBuildVersion
    {
        $version = $this->newVersion($build, $user, 'site', null, $revisionNotes);
        foreach ($version->brief['pages'] as $page) {
            $version->pages()->create(['path' => $page['path'], 'title' => $page['title'], 'status' => SiteBuildPage::PENDING]);
        }

        $this->dispatch($version, withDesign: true, paths: array_column($version->brief['pages'], 'path'));

        return $version;
    }

    /**
     * Regenerate one page. The design and every other page are carried over from the
     * source version, so the new version is complete as soon as the page is written.
     */
    public function regeneratePage(SiteBuild $build, SiteBuildVersion $source, string $path, User $user, ?string $revisionNotes): SiteBuildVersion
    {
        if (! $source->isUsable() || ! $source->pages->contains('path', $path)) {
            throw new RuntimeException('That page cannot be regenerated from this version.');
        }

        $version = $this->newVersion($build, $user, 'page', $path, $revisionNotes);
        $version->update(['design' => $source->design, 'brief' => array_replace($version->brief, ['pages' => $source->brief['pages']])]);

        foreach ($source->pages as $page) {
            $regenerate = $page->path === $path;
            $version->pages()->create([
                'path' => $page->path,
                'title' => $page->title,
                'status' => $regenerate ? SiteBuildPage::PENDING : $page->status,
                'meta' => $regenerate ? null : $page->meta,
                'body_html' => $regenerate ? null : $page->body_html,
                'error' => $regenerate ? null : $page->error,
            ]);
        }
        $version->pages()->where('path', '!=', $path)->where('status', SiteBuildPage::READY)->update(['status' => SiteBuildPage::COPIED]);

        $this->dispatch($version, withDesign: false, paths: [$path]);

        return $version;
    }

    /** Write the version's site files to a temporary zip and return its path. */
    public function zip(SiteBuildVersion $version): string
    {
        $path = tempnam(sys_get_temp_dir(), 'site').'.zip';
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the zip file.');
        }
        foreach ($this->assembler->files($version) as $file => $contents) {
            $zip->addFromString($file, $contents);
        }
        $zip->close();

        return $path;
    }

    public function zipName(SiteBuildVersion $version): string
    {
        return Str::slug($version->build->name ?: 'website').'-v'.$version->number.'.zip';
    }

    private function newVersion(SiteBuild $build, User $user, string $scope, ?string $path, ?string $notes): SiteBuildVersion
    {
        return DB::transaction(function () use ($build, $user, $scope, $path, $notes) {
            SiteBuild::whereKey($build->id)->lockForUpdate()->first();
            if ($build->activeVersion()) {
                throw new RuntimeException('This site is already being generated. Wait for that run to finish.');
            }

            $brief = $build->brief();
            $brief['logo_url'] = $build->logo_path ? '/'.SiteAssembler::logoFileName($build->logo_path) : null;

            return $build->versions()->create([
                'number' => (int) $build->versions()->max('number') + 1,
                'status' => SiteBuildVersion::QUEUED,
                'scope' => $scope,
                'scope_path' => $path,
                'revision_notes' => $notes ?: null,
                'brief' => $brief,
                'created_by' => $user->id,
            ]);
        });
    }

    /** @param list<string> $paths pages to generate, in order */
    private function dispatch(SiteBuildVersion $version, bool $withDesign, array $paths): void
    {
        $jobs = $withDesign ? [new GenerateSiteDesign($version->id)] : [];
        foreach ($paths as $path) {
            $jobs[] = new GenerateSitePage($version->id, $path);
        }
        $jobs[] = new FinalizeSiteVersion($version->id);

        $versionId = $version->id;
        Bus::chain($jobs)
            ->onConnection(config('site_builder.queue.connection'))
            ->onQueue(config('site_builder.queue.name'))
            ->catch(function (Throwable $e) use ($versionId) {
                SiteBuildVersion::whereKey($versionId)->update([
                    'status' => SiteBuildVersion::FAILED,
                    'error' => Str::limit($e->getMessage(), 1000),
                    'finished_at' => now(),
                ]);
            })
            ->dispatch();
    }

    private function storeLogo(SiteBuild $build, ?UploadedFile $logo): void
    {
        if (! $logo) {
            return;
        }
        $disk = Storage::disk(config('site_builder.disk'));
        if ($build->logo_path) {
            $disk->delete($build->logo_path);
        }
        $path = $logo->storeAs('site-builder/logos', $build->id.'-'.Str::random(8).'.'.strtolower($logo->extension()), config('site_builder.disk'));
        $build->update(['logo_path' => $path]);
    }
}
