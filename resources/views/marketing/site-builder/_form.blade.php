{{--
  The site brief, shared by create and edit. Expects $build (null on create),
  $trackedSites and $pages (the page rows to render).
--}}
@php
    $val = fn (string $key, $default = '') => old($key, data_get($build, $key, $default) ?? $default);
    $input = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500';
    $label = 'block text-[12px] font-semibold text-slate-700 mb-1';
    $keywords = old('seo.keywords', implode(', ', $build?->seo['keywords'] ?? []));
@endphp

@if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-800" role="alert">
        <div class="font-semibold mb-1">Please fix the highlighted fields.</div>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-marketing.panel title="Business" subtitle="What the site is about. The AI uses only what you give it — it won't invent facts." icon="building-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}" for="sbName">Business name</label>
            <input id="sbName" name="name" required maxlength="120" value="{{ $val('name') }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}" for="sbType">Business type</label>
            <select id="sbType" name="business[type]" class="{{ $input }}">
                @foreach(config('site_builder.business_types') as $key => $text)
                    <option value="{{ $key }}" @selected($val('business.type', 'Dentist') === $key)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbSummary">Business brief</label>
            <textarea id="sbSummary" name="business[summary]" rows="5" required maxlength="5000" class="{{ $input }}"
                placeholder="Who you are, who you serve, what makes the practice different…">{{ $val('business.summary') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbServices">Services offered</label>
            <textarea id="sbServices" name="business[services]" rows="3" maxlength="3000" class="{{ $input }}"
                placeholder="e.g. Braces, Invisalign, dental implants, emergency visits">{{ $val('business.services') }}</textarea>
        </div>
        <div>
            <label class="{{ $label }}" for="sbPhone">Phone</label>
            <input id="sbPhone" name="business[phone]" maxlength="40" value="{{ $val('business.phone') }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}" for="sbEmail">Email</label>
            <input id="sbEmail" type="email" name="business[email]" maxlength="190" value="{{ $val('business.email') }}" class="{{ $input }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbStreet">Street address</label>
            <input id="sbStreet" name="business[street]" maxlength="120" value="{{ $val('business.street') }}" class="{{ $input }}">
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:col-span-2">
            @foreach(['city' => 'City', 'state' => 'State', 'postal_code' => 'ZIP code', 'country' => 'Country'] as $key => $text)
                <div>
                    <label class="{{ $label }}" for="sb{{ $key }}">{{ $text }}</label>
                    <input id="sb{{ $key }}" name="business[{{ $key }}]" maxlength="80" value="{{ $val('business.'.$key, $key === 'country' ? 'US' : '') }}" class="{{ $input }}">
                </div>
            @endforeach
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbHours">Opening hours</label>
            <input id="sbHours" name="business[hours]" maxlength="500" value="{{ $val('business.hours') }}" class="{{ $input }}"
                placeholder="e.g. Mon–Fri 9am–5pm, Sat 9am–1pm">
        </div>
    </div>
</x-marketing.panel>

<x-marketing.panel title="Brand" subtitle="How the site should look and sound." icon="palette">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}" for="sbLogo">Logo <span class="font-normal text-slate-400">(PNG, JPG, WebP or SVG, up to 2 MB)</span></label>
            <input id="sbLogo" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg"
                class="block w-full text-[12px] text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-[12px] file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
            @if($build?->logo_path)
                <label class="mt-2 inline-flex items-center gap-2 text-[12px] text-slate-600">
                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-emerald-600"> Remove the current logo
                </label>
            @endif
        </div>
        <div>
            <label class="{{ $label }}" for="sbTone">Brand tone</label>
            <select id="sbTone" name="brand[tone]" class="{{ $input }}">
                @foreach(config('site_builder.tones') as $key => $text)
                    <option value="{{ $key }}" @selected($val('brand.tone', 'warm') === $key)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbToneNotes">Tone notes <span class="font-normal text-slate-400">(optional)</span></label>
            <input id="sbToneNotes" name="brand[tone_notes]" maxlength="1000" value="{{ $val('brand.tone_notes') }}" class="{{ $input }}"
                placeholder="e.g. Speak to anxious patients; avoid clinical jargon">
        </div>
        <div class="md:col-span-2 grid grid-cols-3 gap-3">
            @foreach(['primary_color' => ['Primary color', '#0f766e'], 'secondary_color' => ['Secondary color', '#0f172a'], 'accent_color' => ['Accent color', '#f59e0b']] as $key => [$text, $default])
                <div>
                    <label class="{{ $label }}" for="sb{{ $key }}">{{ $text }}</label>
                    <div class="flex items-center gap-2">
                        <input id="sb{{ $key }}" type="color" name="brand[{{ $key }}]" value="{{ $val('brand.'.$key, $default) }}"
                            class="h-9 w-12 rounded-lg border border-slate-200 bg-white p-1 cursor-pointer">
                        <span class="text-[12px] font-mono text-slate-500" data-sb-color-label="sb{{ $key }}">{{ $val('brand.'.$key, $default) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbFonts">Font preference <span class="font-normal text-slate-400">(optional)</span></label>
            <input id="sbFonts" name="brand[fonts]" maxlength="200" value="{{ $val('brand.fonts') }}" class="{{ $input }}"
                placeholder="e.g. A rounded, friendly sans-serif">
        </div>
    </div>
</x-marketing.panel>

<x-marketing.panel title="Pages" subtitle="Each page and its URL. A home page at / is required." icon="files">
    <x-slot:actions>
        <button type="button" data-sb-add-page
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add page
        </button>
    </x-slot:actions>

    <div class="hidden md:grid grid-cols-[1fr_1fr_2fr_2rem] gap-3 px-1 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
        <span>Title</span><span>URL</span><span>What the page should cover <span class="normal-case font-normal">(optional)</span></span><span></span>
    </div>
    <div class="space-y-2" data-sb-pages>
        @foreach($pages as $i => $page)
            @include('marketing.site-builder._page-row', ['i' => $i, 'page' => $page, 'input' => $input])
        @endforeach
    </div>
    <template data-sb-page-template>
        @include('marketing.site-builder._page-row', ['i' => '__INDEX__', 'page' => ['title' => '', 'path' => '', 'notes' => ''], 'input' => $input])
    </template>
    <p class="mt-2 text-[11px] text-slate-400">Up to {{ config('site_builder.max_pages') }} pages.</p>
</x-marketing.panel>

<x-marketing.panel title="SEO" subtitle="Keywords are worked into headings and copy naturally." icon="search">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbKeywords">Target keywords <span class="font-normal text-slate-400">(comma or line separated)</span></label>
            <textarea id="sbKeywords" name="seo[keywords]" rows="3" maxlength="2000" class="{{ $input }}"
                placeholder="orthodontist detroit, invisalign near me, braces for kids">{{ $keywords }}</textarea>
        </div>
        <div>
            <label class="{{ $label }}" for="sbLocation">Target area</label>
            <input id="sbLocation" name="seo[location]" maxlength="190" value="{{ $val('seo.location') }}" class="{{ $input }}"
                placeholder="e.g. Detroit, MI">
        </div>
        <div>
            <label class="{{ $label }}" for="sbDomain">Website domain <span class="font-normal text-slate-400">(for canonical URLs and the sitemap)</span></label>
            <input id="sbDomain" name="domain" maxlength="190" value="{{ $val('domain') }}" class="{{ $input }}" placeholder="example.com">
        </div>
    </div>
</x-marketing.panel>

<x-marketing.panel title="Tracking & extras" icon="radar">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}" for="sbTracking">Growth Engine tracking</label>
            <select id="sbTracking" name="marketing_site_id" class="{{ $input }}">
                <option value="">Don't add tracking</option>
                @foreach($trackedSites as $site)
                    <option value="{{ $site->id }}" @selected((string) $val('marketing_site_id') === (string) $site->id)>{{ $site->name }} — {{ $site->domain }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-[11px] text-slate-500">
                Adds that site's tracking snippet so visits show under Marketing › Websites.
                <a href="{{ route('marketing.tracking') }}" class="font-semibold text-emerald-700 hover:underline">Add a tracked site</a>
            </p>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}" for="sbInstructions">Anything else for the AI? <span class="font-normal text-slate-400">(optional)</span></label>
            <textarea id="sbInstructions" name="instructions" rows="3" maxlength="3000" class="{{ $input }}"
                placeholder="e.g. Emphasize same-day emergency appointments; mention we speak Spanish">{{ $val('instructions') }}</textarea>
        </div>
    </div>
</x-marketing.panel>

<script>
    (function () {
        var list = document.querySelector('[data-sb-pages]');
        var template = document.querySelector('[data-sb-page-template]');
        var max = {{ (int) config('site_builder.max_pages') }};
        var next = list.children.length;

        document.querySelector('[data-sb-add-page]').addEventListener('click', function () {
            if (list.children.length >= max) return;
            list.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, String(next++)));
            if (window.lucide) lucide.createIcons();
        });
        list.addEventListener('click', function (e) {
            var remove = e.target.closest('[data-sb-remove-page]');
            if (remove && list.children.length > 1) remove.closest('[data-sb-page]').remove();
        });
        // Suggest a URL from the title while the URL is still empty.
        list.addEventListener('input', function (e) {
            if (!e.target.matches('[data-sb-title]')) return;
            var path = e.target.closest('[data-sb-page]').querySelector('[data-sb-path]');
            if (path.dataset.touched) return;
            var slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            path.value = slug ? '/' + slug + '/' : '';
        });
        list.addEventListener('change', function (e) {
            if (e.target.matches('[data-sb-path]')) e.target.dataset.touched = '1';
        });
        document.querySelectorAll('input[type=color]').forEach(function (picker) {
            picker.addEventListener('input', function () {
                var label = document.querySelector('[data-sb-color-label="' + picker.id + '"]');
                if (label) label.textContent = picker.value;
            });
        });
    })();
</script>
