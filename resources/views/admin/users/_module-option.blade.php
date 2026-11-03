{{-- One module checkbox in the permissions matrix, with its page checkboxes when the module
     is split into pages (ModuleManager `pages`, e.g. the Growth Engine).
     $modKey, $mod, $catKey, $checked (list of granted permission keys). --}}
@php $pages = $mod['pages'] ?? []; @endphp
<div class="rounded-lg bg-white border border-slate-200/80 hover:border-blue-200 transition-colors">
    <label class="flex items-start gap-3 p-2 cursor-pointer">
        <input type="checkbox" name="modules[]" value="{{ $modKey }}" data-category="{{ $catKey }}"
            @if ($pages !== []) data-paged-module="{{ $modKey }}" @endif
            {{ in_array($modKey, $checked, true) ? 'checked' : '' }}
            class="module-checkbox mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-0 accent-blue-600">
        <div class="flex-1">
            <div class="flex items-center gap-1.5">
                <i data-lucide="{{ $mod['icon'] }}" class="w-3.5 h-3.5 text-blue-600 shrink-0"></i>
                <span class="text-xs font-bold text-slate-900">{{ $mod['name'] }}</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $mod['description'] }}</p>
        </div>
    </label>

    @if ($pages !== [])
        <div class="border-t border-slate-100 px-2 pb-2 pt-1.5 ml-7">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Pages they can open</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-3 gap-y-1">
                @foreach ($pages as $pageKey => $page)
                    @php $permission = \App\Support\ModuleManager::pageKey($modKey, $pageKey); @endphp
                    <label class="flex items-center gap-2 text-[11px] text-slate-700 cursor-pointer">
                        <input type="checkbox" name="modules[]" value="{{ $permission }}" data-category="{{ $catKey }}" data-page-of="{{ $modKey }}"
                            {{ in_array($permission, $checked, true) ? 'checked' : '' }}
                            class="module-checkbox w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-0 accent-blue-600">
                        <span>{{ $page['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>

@once
    <script>
        // A page needs its module, and a module split into pages needs at least one page:
        // ticking a page ticks its module; ticking the module alone ticks all its pages;
        // unticking the module unticks its pages.
        document.addEventListener('change', function (e) {
            var box = e.target;
            if (box.dataset.pageOf) {
                var module = document.querySelector('[data-paged-module="' + box.dataset.pageOf + '"]');
                if (box.checked && module) module.checked = true;
            } else if (box.dataset.pagedModule) {
                var pages = document.querySelectorAll('[data-page-of="' + box.dataset.pagedModule + '"]');
                var anyPage = Array.prototype.some.call(pages, function (p) { return p.checked; });
                pages.forEach(function (p) { if (!box.checked) p.checked = false; else if (!anyPage) p.checked = true; });
            }
        });
    </script>
@endonce
