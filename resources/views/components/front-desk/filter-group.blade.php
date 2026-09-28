{{--
  x-front-desk.filter-group — a segmented filter whose state lives in the URL
  (?{param}={value}). Each option is a plain link, so filters survive reload and share.

  Props:
    param   (required) — query-string key, e.g. "range"
    options (required) — value => label (e.g. FrontDeskFilter::RANGES)
    current (required) — the active value
--}}
@props([
    'param',
    'options',
    'current',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center p-0.5 rounded-lg border border-slate-200 bg-white']) }} role="group">
    @foreach($options as $value => $label)
        @php $on = (string) $value === (string) $current; @endphp
        <a href="{{ request()->fullUrlWithQuery([$param => $value]) }}"
            class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition-colors {{ $on ? 'bg-blue-600 text-white' : 'text-slate-600 hover:text-slate-900' }}"
            @if($on) aria-current="true" @endif>{{ $label }}</a>
    @endforeach
</div>
