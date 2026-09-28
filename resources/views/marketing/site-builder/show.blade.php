<x-marketing-layout>
    <x-slot:title>{{ $build->name }}</x-slot:title>
    <x-slot:subtitle>{{ $build->domain ?? 'AI Website Builder' }}{{ $version ? ' · version '.$version->number : '' }}</x-slot:subtitle>
    <x-slot:toolbar>
        <a href="{{ route('marketing.site-builder.edit', $build) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit brief
        </a>
        <button type="button" data-sb-open="sbRegenerateSite" @disabled($version?->isActive())
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Regenerate site
        </button>
        @if($version?->isUsable())
            <a href="{{ route('marketing.site-builder.download', [$build, $version]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[12px] font-semibold hover:bg-emerald-700">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download .zip
            </a>
        @endif
    </x-slot:toolbar>

    @php
        $done = $version ? $version->pages->filter(fn ($p) => $p->hasContent() || $p->status === 'failed')->count() : 0;
        $total = $version ? $version->pages->count() : 0;
    @endphp

    <div class="p-6 space-y-4 max-w-[1600px]">
        <x-marketing.flash />

        @if(! $version)
            <x-marketing.panel title="Not generated yet" icon="sparkles">
                <x-marketing.empty icon="sparkles" title="This site hasn't been generated" message="Use Regenerate site to build it from the brief." />
            </x-marketing.panel>
        @else
            {{-- Run status. --}}
            @if($version->isActive())
                <div class="flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-[13px] text-sky-900" data-sb-progress>
                    <i data-lucide="loader-circle" class="w-4 h-4 animate-spin shrink-0"></i>
                    <span data-sb-progress-text>
                        @if($version->status === 'queued') Waiting for a worker…
                        @elseif($version->status === 'designing') Designing the look: colors, type, header and footer…
                        @else Writing pages: {{ $done }} of {{ $total }} done…
                        @endif
                    </span>
                </div>
            @elseif($version->status === 'failed')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-800" role="alert">
                    <span class="font-semibold">This run failed.</span> {{ $version->error }}
                </div>
            @elseif($version->status === 'partial')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
                    Some pages couldn't be written. They show a placeholder in the download — regenerate them individually.
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-[320px_minmax(0,1fr)] gap-4 items-start">
                <div class="space-y-4">
                    {{-- Pages of the selected version. --}}
                    <x-marketing.panel title="Pages" icon="files">
                        <ul class="-mx-2 space-y-0.5">
                            @foreach($version->pages as $item)
                                @php $on = $page && $page->id === $item->id; @endphp
                                <li class="flex items-center gap-2 rounded-lg px-2 py-1.5 {{ $on ? 'bg-emerald-50' : 'hover:bg-slate-50' }}" data-sb-page-row="{{ $item->path }}">
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $item->path]) }}" class="min-w-0 flex-1">
                                        <span class="block text-[13px] font-medium text-slate-800 truncate">{{ $item->title }}</span>
                                        <span class="block text-[11px] font-mono text-slate-400 truncate">{{ $item->path }}</span>
                                    </a>
                                    <span data-sb-page-status>@include('marketing.site-builder._status', ['status' => $item->status])</span>
                                    @if($version->isUsable())
                                        <button type="button" data-sb-open="sbRegeneratePage" data-sb-path="{{ $item->path }}" data-sb-title="{{ $item->title }}"
                                            class="p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-white" title="Regenerate this page">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </x-marketing.panel>

                    {{-- Version history. --}}
                    <x-marketing.panel title="Versions" icon="history">
                        <ul class="-mx-2 space-y-0.5">
                            @foreach($versions as $item)
                                @php $on = $item->id === $version->id; @endphp
                                <li>
                                    <a href="{{ route('marketing.site-builder.show', [$build, 'version' => $item->id]) }}"
                                        class="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 {{ $on ? 'bg-emerald-50' : 'hover:bg-slate-50' }}">
                                        <span class="min-w-0">
                                            <span class="block text-[13px] font-medium text-slate-800">
                                                v{{ $item->number }}
                                                @if($item->id === $build->current_version_id)<span class="ml-1 text-[10px] font-bold uppercase text-emerald-600">current</span>@endif
                                            </span>
                                            <span class="block text-[11px] text-slate-500 truncate">
                                                {{ $item->scope === 'page' ? 'Page '.$item->scope_path : 'Full site' }} · {{ $item->created_at->diffForHumans() }}
                                            </span>
                                        </span>
                                        @include('marketing.site-builder._status', ['status' => $item->status])
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-[11px] text-slate-400">
                            v{{ $version->number }}: {{ ops_fmt($version->input_tokens + $version->cache_read_tokens, 'number') }} input · {{ ops_fmt($version->output_tokens, 'number') }} output tokens
                        </p>
                    </x-marketing.panel>

                    <form method="POST" action="{{ route('marketing.site-builder.destroy', $build) }}"
                        onsubmit="return confirm('Delete this website and all its versions?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[12px] font-semibold text-rose-600 hover:underline">Delete website</button>
                    </form>
                </div>

                {{-- Preview. Scripts are blocked twice: no allow-scripts in the sandbox, and a CSP on the response. --}}
                <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <header class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-slate-100">
                        <span class="text-[12px] font-mono text-slate-500 truncate">{{ $page?->path }}</span>
                        <div class="flex items-center gap-2">
                            <div data-dds-panels="device" class="inline-flex items-center p-0.5 rounded-lg border border-slate-200">
                                @foreach(['desktop' => 'monitor', 'mobile' => 'smartphone'] as $device => $icon)
                                    <button type="button" data-dds-panel-tab="{{ $device }}" aria-label="{{ ucfirst($device) }} preview"
                                        class="p-1.5 rounded-md text-slate-500 aria-selected:bg-slate-900 aria-selected:text-white">
                                        <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endforeach
                            </div>
                            @if($page && $version->design)
                                <a href="{{ route('marketing.site-builder.preview', [$build, $version, \App\Domain\SiteBuilder\SitePath::file($page->path)]) }}" target="_blank" rel="noopener"
                                    class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-50" title="Open in a new tab"><i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>
                            @endif
                        </div>
                    </header>
                    @if($page && $version->design && ($page->hasContent() || ! $version->isActive()))
                        @php $src = route('marketing.site-builder.preview', [$build, $version, \App\Domain\SiteBuilder\SitePath::file($page->path)]); @endphp
                        <div class="bg-slate-100 p-3">
                            <div data-dds-panel-for="device" data-dds-panel="desktop">
                                <iframe src="{{ $src }}" title="Preview of {{ $page->title }}" sandbox="allow-same-origin"
                                    class="w-full h-[calc(100vh-15rem)] min-h-[520px] rounded-lg bg-white border border-slate-200"></iframe>
                            </div>
                            <div data-dds-panel-for="device" data-dds-panel="mobile" hidden>
                                <iframe src="{{ $src }}" title="Mobile preview of {{ $page->title }}" sandbox="allow-same-origin" loading="lazy"
                                    class="mx-auto block w-[390px] h-[calc(100vh-15rem)] min-h-[520px] rounded-2xl bg-white border-4 border-slate-800"></iframe>
                            </div>
                        </div>
                    @else
                        <x-marketing.empty icon="loader-circle" title="Preview appears when this page is written" />
                    @endif
                </section>
            </div>
        @endif
    </div>

    {{-- Modal forms (opened with DDS.modal). --}}
    <template id="sbRegenerateSite">
        <form method="POST" action="{{ route('marketing.site-builder.generate', $build) }}" class="w-[min(560px,90vw)] space-y-3 pt-1">
            @csrf
            <h3 class="text-[15px] font-semibold text-slate-900">Regenerate the whole site</h3>
            <p class="text-[12px] text-slate-500">Builds a new version from the current brief. Earlier versions stay available.</p>
            <textarea name="revision_notes" rows="4" maxlength="3000" placeholder="Optional: what should change? e.g. a bolder hero, shorter copy"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold hover:bg-emerald-700">Regenerate site</button>
            </div>
        </form>
    </template>

    @if($version?->isUsable())
        <template id="sbRegeneratePage">
            <form method="POST" action="{{ route('marketing.site-builder.regenerate-page', [$build, $version]) }}" class="w-[min(560px,90vw)] space-y-3 pt-1">
                @csrf
                <input type="hidden" name="path" data-sb-field="path">
                <h3 class="text-[15px] font-semibold text-slate-900">Regenerate <span data-sb-field="title"></span></h3>
                <p class="text-[12px] text-slate-500">Rewrites this page only; the design and the other pages carry over into a new version.</p>
                <textarea name="revision_notes" rows="4" maxlength="3000" placeholder="What should change on this page?"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold hover:bg-emerald-700">Regenerate page</button>
                </div>
            </form>
        </template>
    @endif

    <script>
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-sb-open]');
            if (!trigger || trigger.disabled) return;
            var modal = DDS.modal.openHtml(document.getElementById(trigger.getAttribute('data-sb-open')).innerHTML);
            if (!modal) return;
            var path = modal.querySelector('[data-sb-field="path"]');
            if (path) path.value = trigger.getAttribute('data-sb-path');
            var title = modal.querySelector('[data-sb-field="title"]');
            if (title) title.textContent = '“' + trigger.getAttribute('data-sb-title') + '”';
            var notes = modal.querySelector('textarea');
            if (notes) notes.focus();
        });

        @if($version?->isActive())
            // Follow the run: update the progress line; reload when a page finishes (so its
            // preview appears) or the run ends.
            var finishedAtLoad = @json($done);
            (function poll() {
                fetch(@json(route('marketing.site-builder.status', [$build, $version])), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (run) {
                        if (!run.active) { window.location.reload(); return; }
                        var done = run.pages.filter(function (p) { return p.status === 'ready' || p.status === 'copied' || p.status === 'failed'; }).length;
                        var text = document.querySelector('[data-sb-progress-text]');
                        if (text) text.textContent = run.status === 'queued' ? 'Waiting for a worker…'
                            : run.status === 'designing' ? 'Designing the look: colors, type, header and footer…'
                            : 'Writing pages: ' + done + ' of ' + run.pages.length + ' done…';
                        if (done > finishedAtLoad) { window.location.reload(); return; }
                        setTimeout(poll, 4000);
                    })
                    .catch(function () { setTimeout(poll, 8000); });
            })();
        @endif
    </script>
</x-marketing-layout>
