{{--
  The location checklist for one ad account (shared by the table and the phone cards).
  Expects $account (AdCampaignReportService::accounts row) and $locations (ClinicRegistry).
--}}
@if (! $account['active'])
    <span class="text-[11px] text-slate-500">Closed at Google — not synced</span>
@else
    <details class="relative">
        <summary class="cursor-pointer list-none text-[11px] rounded-lg border {{ $account['location_keys'] === [] ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-200 bg-white text-slate-700' }} px-2 py-1 inline-flex items-center gap-1.5">
            {{ $account['locations'] === [] ? 'Assign locations…' : implode(', ', $account['locations']) }}
            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
        </summary>
        <form method="POST" action="{{ route('marketing.google-ads.accounts.update', $account['id']) }}"
            class="absolute z-20 mt-1 w-64 bg-white border border-slate-200 rounded-lg shadow-lg p-3 space-y-2">
            @csrf @method('PATCH')
            <div class="max-h-56 overflow-y-auto space-y-1">
                @foreach ($locations as $key => $location)
                    <label class="flex items-center gap-2 py-1 text-[12px] text-slate-700 cursor-pointer">
                        <input type="checkbox" name="locations[]" value="{{ $key }}" @checked(in_array((string) $key, $account['location_keys'], true)) class="accent-emerald-600">
                        {{ $location->name }}
                    </label>
                @endforeach
            </div>
            <button type="submit" class="w-full px-3 py-1.5 text-[11px] font-bold rounded-lg bg-[#001f3f] text-white hover:bg-[#002b57]">Save locations</button>
            <p class="text-[10px] text-slate-500">None ticked = not synced or reported.</p>
        </form>
    </details>
@endif
