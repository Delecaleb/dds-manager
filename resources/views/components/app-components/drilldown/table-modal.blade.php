<div
    class="fixed inset-0 z-[120] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200 ds-limitless-modal">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-5xl flex flex-col max-h-[85vh]">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-lg">
            <h4 class="text-sm font-bold text-gray-900">{{ $title }}</h4>
            <button onclick="this.closest('.ds-limitless-modal').remove()"
                class="text-gray-400 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 chunk-scrollbar">
            {{ $slot }}
        </div>
    </div>
</div>