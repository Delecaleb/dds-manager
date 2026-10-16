{{--
  x-marketing.code-block — a dark code block with a Copy button (DDS.copy, public/js/ui.js).

  Props:
    code   (required) — the exact text to show and copy
    label  — button text, default "Copy"
    large  — a prominent "Copy" button under the block instead of the small one in its top bar
--}}
@props(['code', 'label' => 'Copy', 'large' => false])

@php $codeId = 'code-'.\Illuminate\Support\Str::random(10); @endphp

<div {{ $attributes }}>
    @if ($large)
        <pre id="{{ $codeId }}" class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>{{ $code }}</code></pre>
        <button type="button" data-dds-copy="#{{ $codeId }}"
            class="mt-2 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[12px] font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 cursor-pointer">
            <i data-lucide="copy" class="w-3.5 h-3.5"></i> {{ $label }}
        </button>
    @else
        {{-- The button sits in its own bar, so long lines scroll under nothing. --}}
        <div class="bg-slate-900 rounded-xl overflow-hidden">
            <div class="flex justify-end px-2 pt-2">
                <button type="button" data-dds-copy="#{{ $codeId }}"
                    class="px-2 py-1 text-[10px] font-bold rounded border border-white/20 text-slate-200 hover:bg-white/10 cursor-pointer">
                    {{ $label }}
                </button>
            </div>
            <pre id="{{ $codeId }}" class="text-[11px] leading-relaxed text-slate-100 px-4 pb-4 pt-1 overflow-x-auto"><code>{{ $code }}</code></pre>
        </div>
    @endif
</div>
