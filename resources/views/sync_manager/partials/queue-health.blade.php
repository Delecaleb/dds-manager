{{-- Queue health panel: rendered by SyncManagerController@health, swapped in place by loadQueueHealth(). --}}
@php
    $ago = fn (array $beat) => $beat['minutes_ago'] === null ? 'Never seen' : ($beat['minutes_ago'] === 0 ? 'Just now' : $beat['minutes_ago'].' min ago');
    $levelStyles = [
        'error' => ['bg-rose-50 border-rose-200 text-rose-900', 'x-circle', 'text-rose-600'],
        'warning' => ['bg-amber-50 border-amber-200 text-amber-900', 'alert-triangle', 'text-amber-600'],
        'info' => ['bg-sky-50 border-sky-200 text-sky-900', 'info', 'text-sky-600'],
        'ok' => ['bg-emerald-50 border-emerald-200 text-emerald-900', 'check-circle', 'text-emerald-600'],
    ];
    $facts = [
        ['Cron last ran schedule:run', $ago($health['scheduler']), $health['scheduler_php'] ? 'PHP '.$health['scheduler_php'] : null],
        ['Sync worker last seen', $ago($health['worker']), $health['worker_last_job'] ? 'Last job: '.class_basename($health['worker_last_job']) : null],
        ['Jobs in queue', number_format($health['jobs_total']), $health['jobs_ahead_of_requests'] !== null ? $health['jobs_ahead_of_requests'].' ahead of your requests' : null],
        ['Sync requests', $health['pending_requests'].' queued · '.$health['running_requests'].' running', count($health['missing_request_ids']) ? count($health['missing_request_ids']).' missing from queue' : null],
    ];

    $jobsSpec = [
        'columns' => [
            ['key' => 'queue', 'label' => 'Queue', 'type' => 'text'],
            ['key' => 'job', 'label' => 'Job', 'type' => 'text'],
            ['key' => 'waiting', 'label' => 'Waiting', 'type' => 'number', 'heat' => false],
            ['key' => 'in_progress', 'label' => 'In progress', 'type' => 'number', 'heat' => false],
            ['key' => 'scheduled_later', 'label' => 'Scheduled later', 'type' => 'number', 'heat' => false],
            ['key' => 'max_attempts', 'label' => 'Max attempts', 'type' => 'number', 'heat' => false],
            ['key' => 'oldest_minutes', 'label' => 'Oldest (min)', 'type' => 'number', 'heat' => false],
            ['key' => 'read_by_worker', 'label' => 'Worker reads it', 'type' => 'text'],
        ],
        'rows' => array_map(fn (array $group) => array_merge($group, ['read_by_worker' => $group['read_by_worker'] ? 'Yes' : 'NO']), $health['jobs_by_type']),
    ];

    $failedSpec = [
        'columns' => [
            ['key' => 'id', 'label' => 'ID', 'type' => 'text'],
            ['key' => 'job', 'label' => 'Job', 'type' => 'text'],
            ['key' => 'failed_at', 'label' => 'Failed at', 'type' => 'text'],
            ['key' => 'error', 'label' => 'Error', 'type' => 'text', 'class' => 'whitespace-normal max-w-xl'],
        ],
        'rows' => $health['failed_jobs'],
    ];
@endphp

<div class="space-y-4">
  <div class="space-y-2.5">
    @foreach ($health['diagnosis'] as $finding)
      @php [$box, $icon, $iconColor] = $levelStyles[$finding['level']] ?? $levelStyles['info']; @endphp
      <div class="flex gap-3 p-4 rounded-xl border {{ $box }}">
        <i data-lucide="{{ $icon }}" class="w-5 h-5 shrink-0 mt-0.5 {{ $iconColor }}"></i>
        <div class="space-y-1 min-w-0">
          <p class="text-sm font-bold">{{ $finding['title'] }}</p>
          <p class="text-xs">{{ $finding['detail'] }}</p>
          @if ($finding['fix'])
            <p class="text-xs"><span class="font-bold">Fix:</span> <span class="font-mono break-all">{{ $finding['fix'] }}</span></p>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach ($facts as [$label, $value, $sub])
      <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $value }}</p>
        @if ($sub)
          <p class="text-[11px] text-slate-500 mt-0.5">{{ $sub }}</p>
        @endif
      </div>
    @endforeach
  </div>

  @if (count($health['missing_request_ids']) > 0)
    <button type="button" onclick="requeueMissingRequests(this)" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
      <i data-lucide="refresh-cw" class="w-4 h-4"></i> Re-queue missing requests ({{ count($health['missing_request_ids']) }})
    </button>
  @endif

  <div class="border border-slate-200 rounded-xl overflow-hidden">
    <p class="px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-slate-700 bg-slate-50">
      What is in the queue
      @if ($health['jobs_truncated'])
        <span class="normal-case font-medium text-slate-500">(first 5,000 of {{ number_format($health['jobs_total']) }} inspected)</span>
      @endif
    </p>
    <x-analytics-table :spec="$jobsSpec" :sortable="false" />
  </div>

  @if ($health['failed_jobs'] !== [])
    <div class="border border-slate-200 rounded-xl overflow-hidden">
      <p class="px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-slate-700 bg-slate-50">Recent failed jobs</p>
      <x-analytics-table :spec="$failedSpec" :sortable="false" />
    </div>
  @endif

  <p class="text-[11px] text-slate-400">
    Checked {{ \Illuminate\Support\Carbon::parse($health['checked_at'])->format('H:i:s') }} ·
    {{ $health['worker_count'] }} worker(s) reading {{ implode(', ', $health['worker_queues']) }} ·
    cron every {{ $health['observed_cron_minutes'] ?? $health['cron_interval_minutes'] }} min ·
    config cached: {{ $health['config_cached'] ? 'yes' : 'no' }} ·
    same report in a terminal: <span class="font-mono">php artisan sync:health</span>
  </p>
</div>
