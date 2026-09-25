<x-marketing-layout>
    <x-slot:title>Tracking Script</x-slot:title>
    <x-slot:subtitle>Add a site, install its snippet, watch it verify</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1100px]">

        @if(session('status'))
            <p class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                {{ session('status') }}
            </p>
        @endif

        <x-marketing.panel title="Add a site" subtitle="Each site gets its own key, so its traffic stays separate" icon="globe">
            <form method="POST" action="{{ route('marketing.sites.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Site name</label>
                    <input type="text" name="name" required maxlength="255" placeholder="Plymouth Implants"
                        class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:border-emerald-500">
                    @error('name') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Domain</label>
                    <input type="text" name="domain" required maxlength="255" placeholder="plymouthdental.com"
                        class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:border-emerald-500">
                    @error('domain') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Location</label>
                    <select name="office_id" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white">
                        <option value="">Not set</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-[11px] font-bold rounded-lg border border-emerald-500 bg-emerald-600 text-white hover:bg-emerald-700 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add site
                </button>
            </form>
        </x-marketing.panel>

        @forelse($sites as $site)
            @php
                $snippet = "<!-- DDS Growth Engine -->\n"
                    ."<script>\n"
                    ."  (function (w, d, k) {\n"
                    ."    w.ddsq = w.ddsq || [];\n"
                    ."    w.dds = w.dds || function () { w.ddsq.push(arguments); };\n"
                    ."    var s = d.createElement('script');\n"
                    ."    s.async = 1;\n"
                    ."    s.src = '{$scriptUrl}';\n"
                    ."    s.dataset.key = k;\n"
                    ."    d.head.appendChild(s);\n"
                    ."  })(window, document, '{$site->site_key}');\n"
                    ."</script>";
            @endphp

            <x-marketing.panel :title="$site->name" :subtitle="$site->domain" icon="code">
                <x-slot:actions>
                    @if(! $site->is_active)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">Paused</span>
                    @elseif($site->isVerified())
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">
                            Verified {{ $site->verified_at->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Awaiting first event</span>
                    @endif

                    <a href="{{ route('marketing.websites', ['site' => $site->id]) }}"
                        class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Traffic →</a>

                    <form method="POST" action="{{ route('marketing.sites.toggle', $site) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 cursor-pointer">
                            {{ $site->is_active ? 'Pause' : 'Resume' }}
                        </button>
                    </form>
                </x-slot:actions>

                <p class="text-[11px] font-bold text-slate-700 mb-1.5">1. Paste before &lt;/head&gt; on every page</p>
                <div class="relative">
                    <pre id="snippet-{{ $site->id }}" class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>{{ $snippet }}</code></pre>
                    <button type="button" onclick="copySnippet('snippet-{{ $site->id }}', this)"
                        class="absolute top-2.5 right-2.5 px-2 py-1 text-[10px] font-bold rounded border border-white/20 text-slate-200 hover:bg-white/10 cursor-pointer">
                        Copy
                    </button>
                </div>

                <p class="text-[11px] font-bold text-slate-700 mt-4 mb-1.5">2. Call this when a form is submitted</p>
                <pre class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>dds('signup', { email: 'patient@example.com', phone: '(313) 555-0142', name: 'Jane Doe', form: 'book-consult' });</code></pre>

                <x-marketing.wordpress-install :site-key="$site->site_key" :script-url="$scriptUrl" />

                <p class="mt-3 text-[11px] text-slate-500 leading-relaxed">
                    Site key <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">{{ $site->site_key }}</code> —
                    public by design: it names the site and carries no access of its own.
                </p>
            </x-marketing.panel>
        @empty
            <x-marketing.panel title="No sites yet" icon="globe">
                <x-marketing.empty
                    icon="globe"
                    title="Add your first site above"
                    message="You'll get a snippet to paste into that site's pages. The moment the first page loads, the site verifies itself and traffic starts appearing under Websites." />
            </x-marketing.panel>
        @endforelse

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <x-marketing.panel title="What it collects" subtitle="Deliberately limited" icon="eye">
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc pl-4 leading-relaxed">
                    <li>Page path, title and referrer</li>
                    <li>UTM tags and ad click ids (<code class="text-[10px]">gclid</code>, <code class="text-[10px]">fbclid</code>, <code class="text-[10px]">msclkid</code>)</li>
                    <li>A first-party visitor id, kept in the site's own browser storage</li>
                    <li>Device type and browser, from the user agent</li>
                    <li>Timestamps, so time-to-signup can be measured</li>
                    <li>Whatever the site passes to <code class="text-[10px]">dds('signup', …)</code></li>
                </ul>
            </x-marketing.panel>

            <x-marketing.panel title="What it never collects" subtitle="These sites belong to a dental practice" icon="shield-check">
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc pl-4 leading-relaxed">
                    <li>Keystrokes or form contents — only what is passed at signup</li>
                    <li>Health details: symptoms, treatments, conditions</li>
                    <li>IP addresses (used for the rate limit, never stored)</li>
                    <li>Cookies of any kind on your app's domain — the beacon is sent without credentials</li>
                </ul>
                <p class="mt-3 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-2 leading-relaxed">
                    A visitor reading an implants page is still a health-related signal about a person. Agree the cookie banner and consent wording before installing this on a live site.
                </p>
            </x-marketing.panel>
        </div>
    </div>

    <script>
        /* navigator.clipboard exists only in a secure context (HTTPS or localhost), so over
           plain HTTP this falls back to a hidden textarea + execCommand, and tells the user
           when neither worked rather than failing silently. */
        function copySnippet(id, btn) {
            var el = document.getElementById(id);
            if (!el) return;

            var text = el.innerText;

            function flash(message) {
                var original = btn.dataset.label || btn.textContent;
                btn.dataset.label = original;
                btn.textContent = message;
                setTimeout(function () { btn.textContent = btn.dataset.label; }, 1400);
            }

            function legacyCopy() {
                var area = document.createElement('textarea');
                area.value = text;
                area.setAttribute('readonly', '');
                area.style.position = 'fixed';
                area.style.top = '-1000px';
                document.body.appendChild(area);
                area.select();
                area.setSelectionRange(0, text.length);
                var ok = false;
                try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
                document.body.removeChild(area);
                flash(ok ? 'Copied' : 'Press Ctrl+C');
                if (!ok) selectSnippet(el);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () { flash('Copied'); }, legacyCopy);
            } else {
                legacyCopy();
            }
        }

        /* If copying is blocked entirely, select the snippet so Ctrl+C still works. */
        function selectSnippet(el) {
            var range = document.createRange();
            range.selectNodeContents(el);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    </script>
</x-marketing-layout>
