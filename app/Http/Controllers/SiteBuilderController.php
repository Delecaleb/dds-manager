<?php

namespace App\Http\Controllers;

use App\Domain\SiteBuilder\SiteAssembler;
use App\Domain\SiteBuilder\SiteBuilderService;
use App\Domain\SiteBuilder\SitePath;
use App\Http\Requests\SiteBuildRequest;
use App\Models\MarketingSite;
use App\Models\SiteBuild;
use App\Models\SiteBuildVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Growth Engine — AI Website Builder. A brief goes in; queued jobs have Claude write the
 * site; each run is a version that can be previewed, regenerated and downloaded as a zip.
 */
class SiteBuilderController extends Controller
{
    public function __construct(private readonly SiteBuilderService $sites) {}

    public function index(): View
    {
        return view('marketing.site-builder.index', [
            'builds' => SiteBuild::with(['currentVersion', 'marketingSite'])
                ->withCount('versions')
                ->with(['versions' => fn ($q) => $q->limit(1)])
                ->latest('updated_at')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('marketing.site-builder.create', $this->formData(null));
    }

    public function store(SiteBuildRequest $request): RedirectResponse
    {
        $build = $this->sites->create($request->brief(), $request->file('logo'), $request->user());
        $this->sites->generateSite($build, $request->user());

        return redirect()->route('marketing.site-builder.show', $build)
            ->with('status', 'Your site is being built. Pages appear here as they are written.');
    }

    public function show(Request $request, SiteBuild $build): View
    {
        $versions = $build->versions()->with('pages')->get();
        $version = $versions->firstWhere('id', (int) $request->query('version'))
            ?? $versions->first(fn (SiteBuildVersion $v) => $v->isActive())
            ?? $versions->firstWhere('id', $build->current_version_id)
            ?? $versions->first();

        $path = SitePath::normalize((string) $request->query('page', '/'));
        $page = $version?->pages->firstWhere('path', $path) ?? $version?->pages->first();

        return view('marketing.site-builder.show', [
            'build' => $build,
            'versions' => $versions,
            'version' => $version,
            'page' => $page,
        ]);
    }

    public function edit(SiteBuild $build): View
    {
        return view('marketing.site-builder.edit', $this->formData($build));
    }

    public function update(SiteBuildRequest $request, SiteBuild $build): RedirectResponse
    {
        $this->sites->update($build, $request->brief(), $request->file('logo'), $request->boolean('remove_logo'));

        return redirect()->route('marketing.site-builder.show', $build)
            ->with('status', 'Brief saved. Regenerate the site to apply it.');
    }

    public function generate(Request $request, SiteBuild $build): RedirectResponse
    {
        $notes = $request->validate(['revision_notes' => ['nullable', 'string', 'max:3000']])['revision_notes'] ?? null;

        return $this->startRun(fn () => $this->sites->generateSite($build, $request->user(), $notes), $build);
    }

    public function regeneratePage(Request $request, SiteBuild $build, SiteBuildVersion $version): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:150'],
            'revision_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        return $this->startRun(
            fn () => $this->sites->regeneratePage($build, $version, SitePath::normalize($data['path']), $request->user(), $data['revision_notes'] ?? null),
            $build,
            SitePath::normalize($data['path']),
        );
    }

    /** Progress of a run, polled by the show page while it generates. */
    public function status(SiteBuild $build, SiteBuildVersion $version): JsonResponse
    {
        return response()->json([
            'status' => $version->status,
            'active' => $version->isActive(),
            'error' => $version->error,
            'pages' => $version->pages()->get(['path', 'title', 'status', 'error']),
        ]);
    }

    /**
     * One file of a version's preview. Model-written HTML, so it is served with scripts
     * blocked (CSP) and shown in a sandboxed iframe on the show page.
     */
    public function preview(SiteBuild $build, SiteBuildVersion $version, SiteAssembler $assembler, ?string $file = null): Response
    {
        $file = trim((string) $file, '/');
        $file = $file === '' ? 'index.html' : $file;
        $contents = $assembler->previewFile($version->load('pages'), $file);
        abort_if($contents === null, 404);

        $types = ['html' => 'text/html; charset=UTF-8', 'css' => 'text/css; charset=UTF-8', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml'];
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return response($contents, 200, [
            'Content-Type' => $types[$extension] ?? 'application/octet-stream',
            'Content-Security-Policy' => "script-src 'none'; object-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function download(SiteBuild $build, SiteBuildVersion $version): BinaryFileResponse|RedirectResponse
    {
        if (! $version->isUsable()) {
            return back()->with('error', 'This version has no finished pages to download yet.');
        }

        return response()->download($this->sites->zip($version->load('pages', 'build')), $this->sites->zipName($version))
            ->deleteFileAfterSend();
    }

    public function destroy(SiteBuild $build): RedirectResponse
    {
        if ($build->activeVersion()) {
            return back()->with('error', 'Wait for the current run to finish before deleting this site.');
        }
        if ($build->logo_path) {
            Storage::disk(config('site_builder.disk'))->delete($build->logo_path);
        }
        $build->delete();

        return redirect()->route('marketing.site-builder.index')->with('status', 'Site deleted.');
    }

    private function startRun(callable $start, SiteBuild $build, ?string $page = null): RedirectResponse
    {
        try {
            $version = $start();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('marketing.site-builder.show', array_filter([$build, 'version' => $version->id, 'page' => $page]))
            ->with('status', 'Generating. This page updates as the AI writes.');
    }

    private function formData(?SiteBuild $build): array
    {
        return [
            'build' => $build,
            'trackedSites' => MarketingSite::where('is_active', true)->orderBy('name')->get(['id', 'name', 'domain']),
            'pages' => old('pages', $build?->pages ?? config('site_builder.default_pages')),
        ];
    }
}
