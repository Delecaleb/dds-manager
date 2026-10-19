{{--
  Confirm deleting one website, shown in a DDS.modal from the Tracking Script table.
  Expects $row (site row from MarketingController@tracking). Deleting erases its history,
  so the domain must be typed; pausing is offered as the way to keep it.
--}}
@php
    $site = $row['model'];
    $failed = (int) old('site_id') === $site->id ? $errors->first('confirm_domain') : null;
@endphp

<div class="dds-modal" role="dialog" aria-modal="true" aria-labelledby="siteDeleteTitle{{ $site->id }}">
    <div class="dds-modal-panel max-w-md">
        <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-slate-100">
            <h4 id="siteDeleteTitle{{ $site->id }}" class="text-sm font-bold text-slate-900">Delete {{ $site->domain }}?</h4>
            <button type="button" data-dds-close class="text-slate-400 hover:text-slate-600 text-xl leading-none" aria-label="Close">&times;</button>
        </div>

        <form method="POST" action="{{ route('marketing.sites.destroy', $site) }}" class="px-5 py-4 space-y-3">
            @csrf @method('DELETE')
            <input type="hidden" name="site_id" value="{{ $site->id }}">

            <p class="text-[12px] text-rose-900 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2 leading-relaxed">
                This permanently deletes the website and everything it tracked:
                <span class="font-bold">{{ number_format($row['history']['visits']) }} {{ \Illuminate\Support\Str::plural('visit', $row['history']['visits']) }}</span> and
                <span class="font-bold">{{ number_format($row['history']['leads']) }} {{ \Illuminate\Support\Str::plural('lead', $row['history']['leads']) }}</span>.
                The code on the website stops recording. This cannot be undone.
            </p>
            <p class="text-[11px] text-slate-500">To stop tracking but keep the history, pause it instead from the website's details.</p>

            <div>
                <label for="confirmDomain{{ $site->id }}" class="block text-[11px] font-bold text-slate-600 mb-1">Type <span class="font-mono text-slate-900">{{ $site->domain }}</span> to confirm</label>
                <input id="confirmDomain{{ $site->id }}" name="confirm_domain" type="text" autocomplete="off" required
                    class="w-full text-xs rounded-lg border {{ $failed ? 'border-rose-400' : 'border-slate-200' }} px-3 py-2">
                @if ($failed)
                    <p class="text-[10px] text-rose-600 mt-1">{{ $failed }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" data-dds-close class="px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 cursor-pointer">Cancel</button>
                <button type="submit" class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 cursor-pointer">Delete website</button>
            </div>
        </form>
    </div>
</div>
