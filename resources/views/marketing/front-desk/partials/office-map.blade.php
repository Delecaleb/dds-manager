{{--
  Office map panel. Drawing the map needs each office's coordinates, which come with the
  office address (Settings › General); until then the panel lists the offices by status.
  Expects $offices (FrontDeskSource::offices) and optional $title.
--}}
@php $mapped = array_filter($offices, fn ($o) => $o['lat'] !== null && $o['lng'] !== null); @endphp

<section class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex flex-col min-h-[420px]">
    <div class="flex items-center justify-between gap-4">
        <h3 class="text-[13px] font-semibold text-slate-800">{{ $title ?? 'Offices' }}</h3>
        <div class="flex items-center gap-3 text-[11px] text-slate-600">
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>Active</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Onboarding</span>
        </div>
    </div>

    <div class="mt-3 flex-1 rounded-lg bg-slate-50 border border-slate-100 p-4">
        @if($mapped === [])
            <x-marketing.empty icon="map" title="Map appears once office addresses are set"
                message="Each office's address in Settings › General places it on the map." />
            <ul class="mt-2 divide-y divide-slate-100 max-w-md mx-auto">
                @foreach($offices as $office)
                    <li class="flex items-center justify-between gap-3 py-2 text-[12px]">
                        <a href="{{ route('marketing.front-desk.office.home', $office['key']) }}" class="font-medium text-slate-700 hover:text-blue-700 truncate">{{ $office['name'] }}</a>
                        @include('marketing.front-desk.partials.office-status', ['status' => $office['status']])
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
