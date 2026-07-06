@props(['title', 'icon' => 'construction'])

<header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between gap-4 shadow-sm">
    <div class="flex items-center gap-2 text-slate-500 font-medium">
        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        <span>{{ $title }}</span>
    </div>
</header>

<main class="p-6 max-w-[1400px] mx-auto">
    <div class="bg-white border border-slate-200 rounded shadow-sm flex flex-col items-center justify-center text-center gap-3 py-24 px-6">
        <i data-lucide="{{ $icon }}" class="w-10 h-10 text-slate-300"></i>
        <h2 class="text-lg font-bold text-slate-700">{{ $title }}</h2>
        <p class="text-slate-400 max-w-md">This module is not available yet. Check back soon.</p>
    </div>
</main>
