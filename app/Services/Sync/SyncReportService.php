<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncReportService
{
    /**
     * Define all trackable sync modules with their table names and display labels.
     *
     * @return array<string, array{label: string, table: string, service_class?: string, icon?: string}>
     */
    public function getModuleDefinitions(): array
    {
        return [
            'patients' => [
                'label' => 'Patients',
                'table' => 'od_patients',
                'service_class' => PatientSyncService::class,
                'icon' => 'user-square',
            ],
            'appointments' => [
                'label' => 'Appointments',
                'table' => 'od_appointments',
                'service_class' => AppointmentSyncService::class,
                'icon' => 'calendar',
            ],
            'procedurelogs' => [
                'label' => 'Procedure Logs',
                'table' => 'od_procedure_logs',
                'service_class' => ProcedureLogSyncService::class,
                'icon' => 'file-text',
            ],
            'payments' => [
                'label' => 'Payments',
                'table' => 'od_payments',
                'service_class' => PaymentSyncService::class,
                'icon' => 'credit-card',
            ],
            'recalls' => [
                'label' => 'Recalls',
                'table' => 'od_recalls',
                'service_class' => RecallSyncService::class,
                'icon' => 'refresh-cw',
            ],
            'adjustments' => [
                'label' => 'Adjustments',
                'table' => 'od_adjustments',
                'service_class' => AdjustmentSyncService::class,
                'icon' => 'sliders',
            ],
            'paysplits' => [
                'label' => 'Pay Splits',
                'table' => 'od_pay_splits',
                'service_class' => PaySplitSyncService::class,
                'icon' => 'pie-chart',
            ],
            'claimprocs' => [
                'label' => 'Claim Procs',
                'table' => 'od_claim_procs',
                'service_class' => ClaimProcSyncService::class,
                'icon' => 'shield',
            ],
            'claimpayments' => [
                'label' => 'Claim Payments',
                'table' => 'od_claim_payments',
                'service_class' => ClaimPaymentSyncService::class,
                'icon' => 'dollar-sign',
            ],
            'patient_balance' => [
                'label' => 'Patient Balances',
                'table' => 'od_patient_balances',
                'service_class' => PatientBalanceSyncService::class,
                'icon' => 'wallet',
            ],
            'treatment_plans' => [
                'label' => 'Treatment Plans',
                'table' => 'treatment_plans',
                'service_class' => TreatmentPlanSyncService::class,
                'icon' => 'clipboard-list',
            ],
            'payplancharges' => [
                'label' => 'Pay Plan Charges',
                'table' => 'od_pay_plan_charges',
                'service_class' => PayPlanChargeSyncService::class,
                'icon' => 'receipt',
            ],
            'schedules' => [
                'label' => 'Schedules',
                'table' => 'od_schedules',
                'service_class' => ScheduleSyncService::class,
                'icon' => 'clock',
            ],
            'deposits' => [
                'label' => 'Deposits',
                'table' => 'od_deposits',
                'service_class' => DepositSyncService::class,
                'icon' => 'landmark',
            ],
            'statements' => [
                'label' => 'Statements',
                'table' => 'od_statements',
                'service_class' => StatementSyncService::class,
                'icon' => 'file-spreadsheet',
            ],
            'providers' => [
                'label' => 'Providers',
                'table' => 'od_providers',
                'service_class' => ProviderSyncService::class,
                'icon' => 'stethoscope',
            ],
            'procedures' => [
                'label' => 'Procedures',
                'table' => 'od_procedures',
                'service_class' => ProcedureSyncService::class,
                'icon' => 'activity',
            ],
            'carriers' => [
                'label' => 'Carriers',
                'table' => 'od_carriers',
                'service_class' => SyncCarrierService::class,
                'icon' => 'building',
            ],
            'insplan' => [
                'label' => 'Insurance Plans',
                'table' => 'od_insplans',
                'service_class' => SyncInsplanService::class,
                'icon' => 'shield-check',
            ],
            'daily_schedule_snapshots' => [
                'label' => 'Daily Snapshots',
                'table' => 'od_daily_schedule_snapshots',
                'icon' => 'camera',
            ],
        ];
    }

    /**
     * Generate the complete sync report for an office.
     *
     * @return array{
     *     office: array{id: int, name: string, is_active: bool},
     *     summary: array{running: int, slow: int, stuck: int, idle: int, total_records: int},
     *     items: array<int, array{
     *         key: string,
     *         sync: string,
     *         icon: string,
     *         status: string,
     *         status_raw: string,
     *         status_badge: string,
     *         status_icon: string,
     *         last_heartbeat: string,
     *         last_heartbeat_timestamp: ?string,
     *         records: int,
     *         records_formatted: string,
     *         last_error: ?string,
     *         can_sync: bool
     *     }>
     * }
     */
    public function getReportForOffice(Office $office): array
    {
        $officeId = $office->id;
        $modules = $this->getModuleDefinitions();

        // Fetch all sync logs for this office
        $syncLogs = SyncLog::withoutGlobalScopes()
            ->where(function ($q) use ($officeId) {
                $q->where('office_id', $officeId)
                    ->orWhere('module', 'like', "office_{$officeId}:%");
            })
            ->get()
            ->keyBy('module');

        $items = [];
        $counts = [
            'running' => 0,
            'slow' => 0,
            'stuck' => 0,
            'idle' => 0,
            'total_records' => 0,
        ];

        $now = Carbon::now();

        foreach ($modules as $key => $def) {
            $tableName = $def['table'];
            $label = $def['label'];
            $icon = $def['icon'] ?? 'database';

            // Find matching sync log
            $log = $this->findMatchingLog($syncLogs, $officeId, $key, $tableName);

            // Calculate Records Count in local database
            $recordsCount = 0;
            if (Schema::hasTable($tableName)) {
                $query = DB::table($tableName);
                if (Schema::hasColumn($tableName, 'office_id')) {
                    $query->where('office_id', $officeId);
                }
                $recordsCount = $query->count();
            }

            $counts['total_records'] += $recordsCount;

            // Calculate Heartbeat & Status
            $statusInfo = $this->evaluateStatusAndHeartbeat($log, $now);

            $counts[$statusInfo['status_type']]++;

            $items[] = [
                'key' => $key,
                'sync' => $label,
                'icon' => $icon,
                'status' => $statusInfo['status_label'],
                'status_raw' => $statusInfo['status_type'],
                'status_badge' => $statusInfo['badge_class'],
                'status_icon' => $statusInfo['status_icon'],
                'last_heartbeat' => $statusInfo['heartbeat_human'],
                'last_heartbeat_timestamp' => $statusInfo['heartbeat_iso'],
                'records' => $recordsCount,
                'records_formatted' => number_format($recordsCount),
                'last_error' => $log?->last_error,
                'can_sync' => ! empty($def['service_class']),
            ];
        }

        return [
            'office' => [
                'id' => $office->id,
                'name' => $office->name,
                'is_active' => (bool) $office->is_active,
            ],
            'summary' => $counts,
            'items' => $items,
        ];
    }

    /**
     * Find the best matching SyncLog record for an office and module.
     */
    private function findMatchingLog($syncLogs, int $officeId, string $key, string $table): ?SyncLog
    {
        $cleanTable = preg_replace('/^od_/', '', $table);
        $cleanTableNoUnderscores = str_replace('_', '', $cleanTable);
        $singularTable = rtrim($cleanTable, 's');
        $singularTableNoUnderscores = rtrim($cleanTableNoUnderscores, 's');
        $singularKey = rtrim($key, 's');

        // Try precise module names
        $possibleKeys = array_unique(array_filter([
            "office_{$officeId}:{$table}",
            "office_{$officeId}:{$cleanTable}",
            "office_{$officeId}:{$singularTable}",
            "office_{$officeId}:{$cleanTableNoUnderscores}",
            "office_{$officeId}:{$singularTableNoUnderscores}",
            "office_{$officeId}:{$key}",
            "office_{$officeId}:{$singularKey}",
            $key,
            $table,
            $cleanTable,
            $singularTable,
        ]));

        foreach ($possibleKeys as $k) {
            if ($syncLogs->has($k)) {
                return $syncLogs->get($k);
            }
        }

        // Check if there's any log starting with office_{id}:table:window
        foreach ($syncLogs as $mKey => $log) {
            foreach ($possibleKeys as $pKey) {
                if (str_starts_with($mKey, $pKey)) {
                    return $log;
                }
            }
        }

        return null;
    }

    /**
     * Evaluate status (Running / Slow / Stuck / Idle) and humanized heartbeat.
     *
     * @return array{
     *     status_type: 'running'|'slow'|'stuck'|'idle',
     *     status_label: string,
     *     status_icon: string,
     *     badge_class: string,
     *     heartbeat_human: string,
     *     heartbeat_iso: ?string
     * }
     */
    private function evaluateStatusAndHeartbeat(?SyncLog $log, Carbon $now): array
    {
        if (! $log) {
            return [
                'status_type' => 'idle',
                'status_label' => '⚪ Idle',
                'status_icon' => '⚪',
                'badge_class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'heartbeat_human' => 'Never',
                'heartbeat_iso' => null,
            ];
        }

        // Determine most recent heartbeat timestamp
        $heartbeat = null;
        $timestamps = array_filter([
            $log->updated_at ? Carbon::parse($log->updated_at) : null,
            $log->last_synced_at ? Carbon::parse($log->last_synced_at) : null,
            $log->finished_at ? Carbon::parse($log->finished_at) : null,
            $log->started_at ? Carbon::parse($log->started_at) : null,
        ]);

        if (! empty($timestamps)) {
            usort($timestamps, fn ($a, $b) => $b->timestamp <=> $a->timestamp);
            $heartbeat = $timestamps[0];
        }

        $diffSeconds = $heartbeat ? max(0, (int) ($now->timestamp - $heartbeat->timestamp)) : PHP_INT_MAX;
        $heartbeatHuman = $heartbeat ? $this->formatHeartbeat($diffSeconds) : 'Never';
        $heartbeatIso = $heartbeat?->toIso8601String();

        $rawStatus = strtolower(trim((string) ($log->status ?? 'idle')));

        if ($rawStatus === 'running') {
            if ($diffSeconds <= 120) {
                // Heartbeat within last 2 minutes -> Running
                return [
                    'status_type' => 'running',
                    'status_label' => '🟢 Running',
                    'status_icon' => '🟢',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'heartbeat_human' => $heartbeatHuman,
                    'heartbeat_iso' => $heartbeatIso,
                ];
            } elseif ($diffSeconds <= 600) {
                // Heartbeat between 2 and 10 minutes -> Slow
                return [
                    'status_type' => 'slow',
                    'status_label' => '🟡 Slow',
                    'status_icon' => '🟡',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'heartbeat_human' => $heartbeatHuman,
                    'heartbeat_iso' => $heartbeatIso,
                ];
            } else {
                // Running flag without heartbeat for > 10 minutes -> Stuck
                return [
                    'status_type' => 'stuck',
                    'status_label' => '🔴 Stuck',
                    'status_icon' => '🔴',
                    'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'heartbeat_human' => $heartbeatHuman,
                    'heartbeat_iso' => $heartbeatIso,
                ];
            }
        }

        if ($rawStatus === 'failed') {
            return [
                'status_type' => 'stuck',
                'status_label' => '🔴 Stuck',
                'status_icon' => '🔴',
                'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'heartbeat_human' => $heartbeatHuman,
                'heartbeat_iso' => $heartbeatIso,
            ];
        }

        // Completed or idle
        return [
            'status_type' => 'idle',
            'status_label' => '⚪ Idle',
            'status_icon' => '⚪',
            'badge_class' => 'bg-slate-50 text-slate-600 border-slate-200',
            'heartbeat_human' => $heartbeatHuman,
            'heartbeat_iso' => $heartbeatIso,
        ];
    }

    /**
     * Format elapsed seconds into clean human-readable heartbeat string.
     */
    private function formatHeartbeat(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0 sec ago';
        }

        if ($seconds < 60) {
            return $seconds === 1 ? '1 sec ago' : "{$seconds} sec ago";
        }

        $minutes = (int) floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes === 1 ? '1 min ago' : "{$minutes} min ago";
        }

        $hours = (int) floor($minutes / 60);
        if ($hours < 24) {
            return $hours === 1 ? '1 hour ago' : "{$hours} hours ago";
        }

        $days = (int) floor($hours / 24);

        return $days === 1 ? '1 day ago' : "{$days} days ago";
    }

    /**
     * Trigger immediate synchronization for a specific module for an office.
     *
     * @return array{success: bool, message: string, module: string, office: string}
     */
    public function syncModuleForOffice(Office $office, string $moduleKey): array
    {
        $modules = $this->getModuleDefinitions();

        if (! isset($modules[$moduleKey])) {
            throw new \InvalidArgumentException("Invalid sync module '{$moduleKey}'.");
        }

        $def = $modules[$moduleKey];

        if (empty($def['service_class'])) {
            throw new \InvalidArgumentException("Module '{$def['label']}' does not support direct sync.");
        }

        $serviceClass = $def['service_class'];
        $service = app($serviceClass);

        if (method_exists($service, 'forOffice')) {
            $service->forOffice($office);
        }

        $service->sync();

        return [
            'success' => true,
            'message' => "Successfully synced {$def['label']} for '{$office->name}'.",
            'module' => $moduleKey,
            'office' => $office->name,
        ];
    }
}
