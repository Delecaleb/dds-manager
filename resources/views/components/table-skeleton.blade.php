<div class="w-full h-full flex flex-col justify-start space-y-3 bg-white p-4">
    <!-- Header skeleton -->
    <div class="flex items-center gap-4 pb-3 border-b border-slate-200 animate-pulse">
        <div class="h-4 bg-slate-300 rounded w-6 shrink-0"></div>
        <div class="h-4 bg-slate-300 rounded w-44"></div>
        <div class="h-4 bg-slate-200 rounded w-24"></div>
        <div class="h-4 bg-slate-200 rounded w-32"></div>
        <div class="h-4 bg-slate-200 rounded w-20"></div>
        <div class="h-4 bg-slate-200 rounded w-16"></div>
        <div class="h-4 bg-slate-200 rounded w-20"></div>
        <div class="h-4 bg-slate-200 rounded w-36"></div>
        <div class="h-4 bg-slate-200 rounded w-28"></div>
        <div class="h-4 bg-slate-200 rounded w-20"></div>
        <div class="h-4 bg-slate-200 rounded w-28"></div>
        <div class="h-4 bg-slate-200 rounded w-32"></div>
        <div class="h-4 bg-slate-200 rounded w-40"></div>
        <div class="h-4 bg-slate-200 rounded w-32"></div>
    </div>
    <!-- Rows skeleton -->
    <div class="space-y-3 overflow-hidden">
        @for($i = 0; $i < 14; $i++)
        <div class="flex items-center gap-4 py-2 border-b border-slate-100 animate-pulse" style="opacity: {{ max(0.2, 1 - $i * 0.05) }}">
            <div class="h-4 bg-slate-200 rounded w-6 shrink-0"></div>
            <div class="h-4 bg-slate-200 rounded w-44"></div>
            <div class="h-4 bg-slate-100 rounded w-24"></div>
            <div class="h-4 bg-slate-100 rounded w-32"></div>
            <div class="h-4 bg-slate-100 rounded w-20"></div>
            <div class="h-4 bg-slate-100 rounded w-16"></div>
            <div class="h-4 bg-slate-100 rounded w-20"></div>
            <div class="h-4 bg-slate-100 rounded w-36"></div>
            <div class="h-4 bg-slate-100 rounded w-28"></div>
            <div class="h-4 bg-slate-100 rounded w-20"></div>
            <div class="h-4 bg-slate-100 rounded w-28"></div>
            <div class="h-4 bg-slate-100 rounded w-32"></div>
            <div class="h-4 bg-slate-100 rounded w-40"></div>
            <div class="h-4 bg-slate-100 rounded w-32"></div>
        </div>
        @endfor
    </div>
</div>