{{-- One office's AI Front Desk status pill. $status: active | onboarding | not_connected. --}}
@php
    [$label, $tone] = match ($status) {
        'active' => ['Active', 'emerald'],
        'onboarding' => ['Onboarding', 'amber'],
        default => ['Not connected', 'slate'],
    };
@endphp
<x-front-desk.pill :label="$label" :tone="$tone" />
