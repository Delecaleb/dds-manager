<x-marketing-layout>
    <x-slot:title>Edit Brief</x-slot:title>
    <x-slot:subtitle>{{ $build->name }} · changes apply the next time the site is regenerated</x-slot:subtitle>

    <form method="POST" action="{{ route('marketing.site-builder.update', $build) }}" enctype="multipart/form-data" class="p-6 space-y-5 max-w-[1100px]">
        @csrf
        @method('PUT')
        @include('marketing.site-builder._form')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('marketing.site-builder.show', $build) }}" class="px-4 py-2.5 rounded-lg text-[13px] font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button type="submit" class="px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold hover:bg-emerald-700">Save brief</button>
        </div>
    </form>
</x-marketing-layout>
