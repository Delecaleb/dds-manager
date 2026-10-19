{{--
  One website's install details, shown in a DDS.modal from the Tracking Script table.
  Expects $row (site row from MarketingController@tracking), $locations, $scriptUrl and $isNew.
--}}
@php
    $site = $row['model'];
    $codes = $row['shared'] ? $row['form_codes'] : [];
    $instructions = $site->installInstructions($scriptUrl, $codes);
    $mailto = 'mailto:?subject='.rawurlencode("Website tracking code for {$site->domain}").'&body='.rawurlencode($instructions);
@endphp

<div class="dds-modal" role="dialog" aria-modal="true" aria-labelledby="siteTitle{{ $site->id }}">
    <div class="dds-modal-panel flex flex-col max-h-[90vh]">
        <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="min-w-0">
                <h4 id="siteTitle{{ $site->id }}" class="text-sm font-bold text-slate-900 truncate">{{ $site->name }}</h4>
                <p class="text-[11px] text-slate-500 truncate">{{ $site->domain }} · {{ $row['location'] ?? 'Not assigned to a location' }}{{ $row['shared'] ? ' (shared)' : '' }}</p>
            </div>
            <button type="button" data-dds-close class="text-slate-400 hover:text-slate-600 text-xl leading-none" aria-label="Close">&times;</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
            @if($isNew)
                <p class="text-[12px] text-emerald-900 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                    <span class="font-bold">Ready to install.</span> Copy the code below into {{ $site->domain }}, or send the instructions to whoever manages the website.
                </p>
            @endif

            <div>
                <p class="text-[12px] font-bold text-slate-800 mb-1.5">1. Paste this code before &lt;/head&gt; on every page</p>
                <x-marketing.code-block :code="$site->trackingSnippet($scriptUrl)" label="Copy website code" large />
            </div>

            {{-- Someone else maintains the website: hand them everything in one go. --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] text-slate-500 mr-1">Someone else manages the website?</span>
                <pre id="instructions-{{ $site->id }}" class="hidden">{{ $instructions }}</pre>
                <button type="button" data-dds-copy="#instructions-{{ $site->id }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 cursor-pointer">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Copy instructions for your developer
                </button>
                <a href="{{ $mailto }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
                    <i data-lucide="mail" class="w-3.5 h-3.5"></i> Email instructions
                </a>
            </div>

            <div>
                <p class="text-[12px] font-bold text-slate-800 mb-1.5">2. Record a lead when a form is submitted</p>
                <p class="text-[11px] text-slate-500 mb-1.5">Without this, visits are tracked but contact-form signups are not. Use the form's real values.</p>
                <x-marketing.code-block :code="$site->signupExample($codes === [] ? null : (string) reset($codes))" />
                @if ($codes !== [])
                    <p class="text-[11px] text-slate-500 mt-2 mb-1">Set <code class="text-[10px] bg-slate-100 px-1 rounded">location</code> to the location the patient chose:</p>
                    <ul class="text-[11px] text-slate-700 space-y-0.5">
                        @foreach ($codes as $name => $code)
                            <li><code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">{{ $code }}</code> → {{ $name }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- The locations this website serves, and on a shared site how a visit is placed. --}}
            <form method="POST" action="{{ route('marketing.sites.update', $site) }}" class="border-t border-slate-100 pt-4 space-y-3">
                @csrf @method('PATCH')
                <div>
                    <p class="text-[12px] font-bold text-slate-800">Locations this website serves</p>
                    <p class="text-[11px] text-slate-500">Tick every location that uses this website. One script covers them all.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 max-h-48 overflow-y-auto {{ $row['location_keys'] === [] ? 'rounded-lg border border-amber-300 bg-amber-50 px-2' : '' }}">
                    @foreach ($locations as $key => $location)
                        <label class="flex items-center gap-2 py-1 text-[12px] text-slate-700 cursor-pointer">
                            <input type="checkbox" name="locations[]" value="{{ $key }}" @checked(in_array((string) $key, $row['location_keys'], true)) class="accent-emerald-600">
                            {{ $location->name }}
                        </label>
                    @endforeach
                </div>

                @if ($row['shared'])
                    <div class="rounded-lg border border-sky-200 bg-sky-50/60 p-3 space-y-2">
                        <p class="text-[11px] text-sky-900 leading-relaxed">
                            <span class="font-bold">Shared website.</span> Each visit is credited to a location by, in order:
                            the <span class="font-semibold">location the form sends</span>, then an <span class="font-semibold">OpenDental patient match</span> on the lead's phone or email,
                            then the <span class="font-semibold">pages visited</span>. Visits with no clue count only under "All locations".
                        </p>
                        <div class="space-y-2">
                            @foreach ($row['rules'] as $rule)
                                <div class="grid grid-cols-1 sm:grid-cols-[10rem_9rem_1fr] gap-1.5 sm:items-center">
                                    <span class="text-[11px] font-semibold text-slate-700">{{ $rule['name'] }}</span>
                                    <input type="text" name="codes[{{ $rule['key'] }}]" value="{{ $rule['code'] }}" maxlength="64" title="Value the form sends as location" aria-label="Form value for {{ $rule['name'] }}" class="text-[11px] rounded border border-slate-200 px-2 py-1 bg-white">
                                    <input type="text" name="paths[{{ $rule['key'] }}]" value="{{ $rule['paths'] }}" placeholder="Pages, e.g. /grand-rapids" title="Pages under these paths belong to this location" aria-label="Paths for {{ $rule['name'] }}" class="text-[11px] rounded border border-slate-200 px-2 py-1 bg-white">
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-500">Paths are optional: leave them empty if the website has no separate pages per location. Saving re-credits past visits.</p>
                    </div>
                @endif

                <button type="submit" class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-[#001f3f] text-white hover:bg-[#002b57] cursor-pointer">Save locations</button>
            </form>

            <x-marketing.wordpress-install :site-key="$site->site_key" :script-url="$scriptUrl" />

            {{-- Settings for this website. --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-4">
                <form method="POST" action="{{ route('marketing.sites.toggle', $site) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 cursor-pointer">
                        {{ $site->is_active ? 'Pause tracking' : 'Resume tracking' }}
                    </button>
                </form>

                <span class="text-[11px] text-slate-500">
                    Site key <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">{{ $site->site_key }}</code>
                    <button type="button" data-dds-copy-text="{{ $site->site_key }}" class="ml-1 font-bold text-slate-500 hover:text-slate-800 cursor-pointer">Copy</button>
                    — public by design: it names the site and carries no access.
                </span>
            </div>
        </div>
    </div>
</div>
