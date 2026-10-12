{{--
  API call usage line chart, shared by the Developer Portal, Administration → Developer API and
  a key's activity page. Expects $usage (ApiUsageService::daily), $usageDays and $scope (what
  the calls cover, e.g. "All your keys"). The period links keep the page's other query parameters.
--}}
@php
    $failedShare = $usage['total'] > 0 ? round($usage['failed'] / $usage['total'] * 100, 1) : 0;
    $subtitle = "{$scope} · last {$usageDays} days · ".number_format($usage['failed'])." failed or refused ({$failedShare}%)";
@endphp
<x-front-desk.chart title="API call usage" :subtitle="$subtitle" :data="['labels' => $usage['labels'], 'series' => $usage['series']]"
    :value="number_format($usage['total']).' calls'" :height="240">
    <x-slot:actions>
        <div class="flex items-center gap-1 shrink-0" role="group" aria-label="Period">
            @foreach (\App\Domain\DeveloperApi\ApiUsageService::PERIODS as $period)
                <a href="{{ request()->fullUrlWithQuery(['days' => $period]) }}"
                    @if ($period === $usageDays) aria-current="true" @endif
                    class="px-2 py-1 text-[11px] font-bold rounded-md border {{ $period === $usageDays ? 'border-[#001f3f] bg-[#001f3f] text-white' : 'border-slate-200 text-slate-600 bg-white hover:bg-slate-50' }}">{{ $period }}d</a>
            @endforeach
        </div>
    </x-slot:actions>
</x-front-desk.chart>
