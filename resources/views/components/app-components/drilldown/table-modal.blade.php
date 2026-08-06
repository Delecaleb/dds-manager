@props(['title', 'providerInfo' => null])

<div
    class="fixed inset-0 z-[120] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200 ds-limitless-modal">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-5xl flex flex-col max-h-[85vh]">
        <div class="p-6 border-b border-gray-100 bg-white rounded-t-lg">
            <div class="flex justify-between items-start">
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h2>
                <button onclick="this.closest('.ds-limitless-modal').remove()"
                    class="text-gray-900 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            @if (!empty($providerInfo))
                <div class="grid grid-cols-2 gap-8 pt-4">
                    <div>
                        <p class="text-xs font-bold text-gray-900">Provider</p>
                        <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $providerInfo['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">Provider ID</p>
                        <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $providerInfo['id'] }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex-1 overflow-y-auto p-5 chunk-scrollbar">
            {{ $slot }}
        </div>
    </div>
</div>