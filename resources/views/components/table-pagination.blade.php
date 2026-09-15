@props([
    'id' => 'table',
    'lengths' => [10, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 500, 1000, -1],
    'defaultLength' => 20,
])

<div id="{{ $id }}-pagination-container"
    {{ $attributes->merge(['class' => 'p-4 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-3']) }}>
    <!-- Left: Items per page & counts -->
    <div class="flex items-center">
        <div tabindex="-1" class="flex items-center px-0">
            <label for="{{ $id }}ItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium text-slate-600">Items per page</label>
            <select id="{{ $id }}ItemsPerPage" class="p-1.5 px-2 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-emerald-500 text-xs cursor-pointer">
                @foreach($lengths as $len)
                    <option value="{{ $len }}" {{ (string)$len === (string)$defaultLength ? 'selected' : '' }}>
                        {{ $len == -1 ? ' All ' : ' ' . $len . ' ' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:px-3 md:flex md:items-center text-slate-600">
            <span class="hidden md:inline md:mr-1" id="{{ $id }}RangeInfo">0-0</span> of <span id="{{ $id }}TotalCount" class="font-bold text-slate-800 ml-1 mr-1">0</span> items
        </div>
    </div>

    <!-- Right: Page dropdown selector & Prev/Next buttons -->
    <div class="flex items-center gap-2">
        <div class="flex items-center px-2">
            <div class="mr-2">
                <select id="{{ $id }}PageSelect" class="p-1.5 px-2 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-emerald-500 text-xs cursor-pointer">
                    <option value="1" selected> 1 </option>
                </select>
            </div>
            <span class="text-slate-600">of <span id="{{ $id }}TotalPages" class="font-medium text-slate-800">1</span> <span class="ml-1 hidden md:inline">pages</span></span>
        </div>
        <div class="flex items-center">
            <button id="{{ $id }}PrevBtn" disabled type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Previous Page">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </button>
            <button id="{{ $id }}NextBtn" disabled type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Next Page">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </button>
        </div>
    </div>
</div>
