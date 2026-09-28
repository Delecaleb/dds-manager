<x-marketing-layout>
    <x-slot:title>New Website</x-slot:title>
    <x-slot:subtitle>Describe the business; the AI writes the site</x-slot:subtitle>

    <form method="POST" action="{{ route('marketing.site-builder.store') }}" enctype="multipart/form-data" class="p-6 space-y-5 max-w-[1100px]">
        @csrf
        @include('marketing.site-builder._form')

        <div class="flex items-center justify-end gap-3">
            <p class="text-[12px] text-slate-500">Building takes a few minutes; you can leave the page.</p>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold hover:bg-emerald-700">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Build website
            </button>
        </div>
    </form>
</x-marketing-layout>
