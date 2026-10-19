<x-marketing-layout>
    <x-slot:title>Tracking Script</x-slot:title>
    <x-slot:subtitle>Add a website, copy its code, paste it into the site — it verifies itself</x-slot:subtitle>

    <div class="p-4 sm:p-6 space-y-6 max-w-[1100px]">

        @if(session('status'))
            <p class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                {{ session('status') }}
            </p>
        @endif

        {{-- 1. Add: only the address is required; the name defaults to it. --}}
        <x-marketing.panel title="Add a website" subtitle="Each website gets its own code, so its traffic stays separate" icon="globe">
            <form method="POST" action="{{ route('marketing.sites.store') }}" class="grid grid-cols-1 md:grid-cols-[1.3fr_1fr_1fr_auto] gap-3 items-end">
                @csrf
                <div>
                    <label for="siteDomain" class="block text-[11px] font-bold text-slate-700 mb-1.5">Website address</label>
                    <input id="siteDomain" type="text" name="domain" required maxlength="255" value="{{ old('domain') }}" placeholder="plymouthdental.com" autocomplete="off"
                        class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:border-emerald-500">
                    @error('domain') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="siteLocation" class="block text-[11px] font-bold text-slate-700 mb-1.5">Location</label>
                    <select id="siteLocation" name="location" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white">
                        <option value="">Not assigned</option>
                        @foreach($locations as $key => $location)
                            <option value="{{ $key }}" @selected((string) old('location', $defaultLocation) === (string) $key)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="siteName" class="block text-[11px] font-bold text-slate-700 mb-1.5">Name <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="siteName" type="text" name="name" maxlength="255" value="{{ old('name') }}" placeholder="Same as the address"
                        class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:border-emerald-500">
                    @error('name') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[12px] font-bold rounded-lg border border-emerald-500 bg-emerald-600 text-white hover:bg-emerald-700 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add &amp; get code
                </button>
            </form>
        </x-marketing.panel>

        {{-- 2. Every website in one table; the eye opens its code and settings in a modal. --}}
        <x-marketing.panel title="Websites" :subtitle="count($sites).' '.\Illuminate\Support\Str::plural('website', count($sites)).' · open one to copy its code'" icon="code">
            @if (count($sites) === 0)
                <x-marketing.empty
                    icon="globe"
                    title="Add your first website above"
                    message="Type the website's address and press Add & get code. You'll get one piece of code to paste into the site; the first page view verifies it and traffic starts appearing under Websites." />
            @else
                <div class="p-3">
                    <x-data-table id="mkSites" min-width="760px" data-mk-sites>
                        <x-slot:head>
                            <tr>
                                <th class="px-4 py-3">Website</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Added</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </x-slot:head>
                        @foreach($sites as $row)
                            @php $site = $row['model']; @endphp
                            <tr class="hover:bg-slate-50 {{ $openSiteId === $site->id ? 'bg-emerald-50/60' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="text-[13px] font-semibold text-slate-800">{{ $site->name }}</div>
                                    @if ($site->name !== $site->domain)
                                        <div class="text-[11px] text-slate-500">{{ $site->domain }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[12px] {{ $row['location'] === null ? 'text-amber-700' : 'text-slate-700' }}">
                                    {{ $row['location'] ?? 'Not assigned' }}
                                    @if ($row['shared'])
                                        <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-sky-200 bg-sky-50 text-sky-700" title="One script; each visit is credited to the location it belongs to">Shared</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3" data-order="{{ ! $site->is_active ? 2 : ($site->isVerified() ? 1 : 0) }}">
                                    @if(! $site->is_active)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">Paused</span>
                                    @elseif($site->isVerified())
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700" title="Verified {{ $site->verified_at->format('M j, Y g:i A') }}">Verified</span>
                                    @else
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Not installed yet</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[12px] text-slate-500" data-order="{{ $site->created_at?->timestamp }}">{{ $site->created_at?->format('M j, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                        <button type="button" data-site-open="{{ $site->id }}" title="View code and settings" aria-label="View code and settings for {{ $site->name }}"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 hover:text-emerald-700 cursor-pointer">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <a href="{{ route('marketing.websites', ['site' => $site->id, 'locations' => $row['location_keys'] === [] ? 'all' : implode(',', $row['location_keys'])]) }}" title="See traffic" aria-label="See traffic for {{ $site->name }}"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 hover:text-emerald-700">
                                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                {{-- Each site's details, opened by its eye button through DDS.modal. --}}
                @foreach($sites as $row)
                    <template id="site-details-{{ $row['model']->id }}">
                        @include('marketing.tracking._site-details', ['row' => $row, 'isNew' => $openSiteId === $row['model']->id])
                    </template>
                @endforeach
            @endif
        </x-marketing.panel>

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
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.querySelector('[data-mk-sites]');
            if (table) DDS.dataTable(table, { paging: false, info: false, searching: true, order: [[0, 'asc']] });

            function openSite(id) {
                var template = document.getElementById('site-details-' + id);
                if (template) DDS.modal.openHtml(template.innerHTML);
            }

            document.addEventListener('click', function (e) {
                var button = e.target.closest('[data-site-open]');
                if (button) openSite(button.getAttribute('data-site-open'));
            });

            // A website just added opens straight to its code.
            @if ($openSiteId)
                openSite(@json($openSiteId));
            @endif
        });
    </script>
</x-marketing-layout>
