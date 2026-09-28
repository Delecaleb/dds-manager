{{-- A generation run's (or page's) status pill. $status: a SiteBuildVersion or SiteBuildPage status. --}}
@php
    [$text, $tone] = match ($status) {
        'queued', 'pending' => ['Queued', 'slate'],
        'designing' => ['Designing', 'violet'],
        'building', 'generating' => ['Writing', 'sky'],
        'ready' => ['Ready', 'emerald'],
        'copied' => ['Unchanged', 'slate'],
        'partial' => ['Partly ready', 'amber'],
        'failed' => ['Failed', 'rose'],
        default => [ucfirst((string) $status), 'slate'],
    };
@endphp
<x-front-desk.pill :label="$text" :tone="$tone" />
