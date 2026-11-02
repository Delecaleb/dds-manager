{{--
  x-marketing.traffic-sentence — the website numbers read as one sentence, so how they relate
  is clear before any card is read: "1,240 unique visitors made 1,800 visits and opened
  4,300 pages. 36 of those visits (2.0%) ended in a signup." Each number carries its
  metric's colour from MarketingMetric, matching its card and its table columns.

  Props: summary (required) — WebsiteAnalyticsService::summary()
--}}
@props(['summary'])

@use(App\Domain\Marketing\MarketingMetric)

@php
    $n = fn (string $metric, int $value, string $noun) => '<span class="font-extrabold '
        . MarketingMetric::TONES[MarketingMetric::tone($metric)]['icon'] . '">'
        . e(number_format($value) . ' ' . $noun) . '</span>';
    $plural = fn (int $value, string $one, string $many) => $value === 1 ? $one : $many;
@endphp

<p {{ $attributes->merge(['class' => 'text-[13px] text-slate-700 leading-relaxed']) }}>
    @if ($summary['sessions'] === 0)
        No visits were recorded in this period.
    @else
        {!! $n('visitors', $summary['visitors'], $plural($summary['visitors'], 'unique visitor', 'unique visitors')) !!}
        made {!! $n('visits', $summary['sessions'], $plural($summary['sessions'], 'visit', 'visits')) !!}
        and opened {!! $n('pageviews', $summary['pageviews'] ?? 0, $plural($summary['pageviews'] ?? 0, 'page', 'pages')) !!}.
        {!! $n('signups', $summary['signups'], $plural($summary['signups'], 'visit', 'visits')) !!}
        ({{ $summary['signup_rate'] }}%) ended in a signup.
    @endif
</p>
