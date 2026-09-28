{{-- One row of the Pages list. $i is the row index ("__INDEX__" in the add-row template). --}}
<div data-sb-page class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_2rem] gap-3 items-start rounded-lg md:rounded-none border md:border-0 border-slate-200 p-3 md:p-0">
    <input name="pages[{{ $i }}][title]" value="{{ $page['title'] ?? '' }}" required maxlength="80" placeholder="Page title" aria-label="Page title"
        data-sb-title class="{{ $input }} @error("pages.$i.title") border-rose-400 @enderror">
    <input name="pages[{{ $i }}][path]" value="{{ $page['path'] ?? '' }}" required maxlength="150" placeholder="/about/" aria-label="Page URL"
        data-sb-path @if(filled($page['path'] ?? '')) data-touched="1" @endif
        class="{{ $input }} font-mono @error("pages.$i.path") border-rose-400 @enderror">
    <input name="pages[{{ $i }}][notes]" value="{{ $page['notes'] ?? '' }}" maxlength="1000" placeholder="Key points, sections, calls to action…" aria-label="Page notes"
        class="{{ $input }}">
    <button type="button" data-sb-remove-page class="h-9 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" aria-label="Remove page">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
</div>
