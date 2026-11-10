{{-- A list of named items with a type and an explanation (parameters, response fields).
     Expects $title and $items: list of [name, type, description] or [name, type, required, description]. --}}
<div>
    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">{{ $title }}</h4>
    <dl class="border border-slate-200 rounded-lg divide-y divide-slate-100">
        @foreach ($items as $item)
            @php
                $required = count($item) === 4 ? $item[2] : null;
                $description = $item[count($item) - 1];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,14rem)_minmax(0,1fr)] gap-x-4 gap-y-1 px-3 py-2">
                <dt class="min-w-0">
                    <code class="text-[12px] font-semibold text-slate-900 break-words">{{ $item[0] }}</code>
                    <div class="text-[11px] text-slate-500">
                        {{ $item[1] }}@if ($required === true)<span class="ml-1 font-semibold text-rose-700">required</span>@elseif ($required === false)<span class="ml-1">optional</span>@endif
                    </div>
                </dt>
                <dd class="text-xs text-slate-700 leading-relaxed">{{ $description }}</dd>
            </div>
        @endforeach
    </dl>
</div>
