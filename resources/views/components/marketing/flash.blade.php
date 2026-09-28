{{-- x-marketing.flash — the session's status / error message, if any. --}}
@if(session('status'))
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-medium text-emerald-800']) }} role="status">
        <i data-lucide="circle-check" class="w-4 h-4 shrink-0"></i>{{ session('status') }}
    </div>
@endif
@if(session('error'))
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-medium text-rose-800']) }} role="alert">
        <i data-lucide="circle-alert" class="w-4 h-4 shrink-0"></i>{{ session('error') }}
    </div>
@endif
