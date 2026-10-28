<x-mail::message>
# Sync failed

@if($attempts > 1)
**{{ $data }}** for **{{ $location }}** stopped after {{ $attempts }} attempts (automatic retries used up).
@else
**{{ $data }}** for **{{ $location }}** stopped. It was not retried, because retrying would give the same result.
@endif

**What went wrong:** {{ $problem }}

**What to do:** {{ $action }}

<x-mail::panel>
Failed at: {{ $failedAt }}<br>
Technical details: {{ $technical }}
</x-mail::panel>

<x-mail::button :url="$syncManagerUrl">
Open Sync Manager
</x-mail::button>

The same problem at this location will not be emailed again for {{ $cooldownMinutes >= 60 && $cooldownMinutes % 60 === 0 ? ($cooldownMinutes / 60).' hour(s)' : $cooldownMinutes.' minute(s)' }}. Other failures are listed on the Sync Manager page.

{{ config('app.name') }}
</x-mail::message>
