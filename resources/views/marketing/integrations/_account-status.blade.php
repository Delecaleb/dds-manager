{{-- An ad account's sync status, from AdCampaignReportService::accounts. Expects $account. --}}
@switch($account['state'])
    @case('waiting') <x-front-desk.pill label="Waiting for a location" tone="amber" /> @break
    @case('closed') <x-front-desk.pill :label="ucfirst(strtolower((string) $account['status']))" tone="slate" /> @break
    @case('blocked') <x-front-desk.pill label="Not syncing" tone="rose" /> @break
    @case('failed') <x-front-desk.pill label="Last sync failed" tone="rose" /> @break
    @case('pending') <x-front-desk.pill label="Waiting for first sync" tone="sky" /> @break
    @default <x-front-desk.pill label="Synced" tone="emerald" />
@endswitch
