<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Models\SyncRequest;
use App\Services\Sync\AdjustmentSyncService;
use App\Services\Sync\AppointmentSyncService;
use App\Services\Sync\ClaimProcSyncService;
use App\Services\Sync\HardDeleteSyncService;
use App\Services\Sync\PatientSyncService;
use App\Services\Sync\PaymentSyncService;
use App\Services\Sync\ProcedureLogSyncService;
use App\Services\Sync\TreatmentPlanSyncService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingSyncRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:process-pending {--id= : Process a specific sync request ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending server-to-server date range sync requests';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        // Self-heal any stale 'running' jobs (older than 10 minutes)
        SyncRequest::where('status', 'running')
            ->where('started_at', '<', now()->subMinutes(10))
            ->update([
                'status' => 'failed',
                'error_message' => 'Sync process timed out or was terminated by server.',
                'completed_at' => now(),
            ]);

        $specificId = $this->option('id');

        $query = SyncRequest::where('status', 'pending')->orderBy('id', 'asc');
        if ($specificId) {
            $query = SyncRequest::where('id', $specificId)->whereIn('status', ['pending', 'running']);
        }

        $requests = $query->get();

        if ($requests->isEmpty()) {
            $this->info('No pending sync requests found.');

            return Command::SUCCESS;
        }

        foreach ($requests as $req) {
            $this->processRequest($req);
        }

        return Command::SUCCESS;
    }

    protected function processRequest(SyncRequest $req): void
    {
        $req->update([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
        ]);

        // Register shutdown function to catch fatal errors and prevent stuck jobs
        register_shutdown_function(function () use ($req) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $req->update([
                    'status' => 'failed',
                    'error_message' => 'Fatal PHP Error: '.$error['message'],
                    'completed_at' => now(),
                ]);
            }
        });

        $this->info("Starting sync request #{$req->id} for module '{$req->module}' (Window: {$req->start_date} to {$req->end_date})");

        try {
            $office = Office::find($req->office_id) ?? Office::first();
            $module = strtolower(trim($req->module));
            $startDate = $req->start_date ? $req->start_date->format('Y-m-d') : null;
            $endDate = $req->end_date ? $req->end_date->format('Y-m-d') : null;

            $moduleServiceMap = [
                'appointment' => AppointmentSyncService::class,
                'appointments' => AppointmentSyncService::class,
                'procedurelog' => ProcedureLogSyncService::class,
                'procedurelogs' => ProcedureLogSyncService::class,
                'patient' => PatientSyncService::class,
                'patients' => PatientSyncService::class,
                'adjustment' => AdjustmentSyncService::class,
                'adjustments' => AdjustmentSyncService::class,
                'payment' => PaymentSyncService::class,
                'payments' => PaymentSyncService::class,
                'claimproc' => ClaimProcSyncService::class,
                'claimprocs' => ClaimProcSyncService::class,
                'treatmentplan' => TreatmentPlanSyncService::class,
                'treatmentplans' => TreatmentPlanSyncService::class,
            ];

            if ($module === 'all') {
                $modulesToRun = array_unique(array_values($moduleServiceMap));
                foreach ($modulesToRun as $serviceClass) {
                    $service = app($serviceClass)->forOffice($office);
                    if ($startDate || $endDate) {
                        try {
                            $service->withDateWindow($startDate, $endDate);
                        } catch (Exception $e) {
                            // If service doesn't support dateWindow, skip window for that service
                        }
                    }
                    $service->sync();
                }
            } elseif (isset($moduleServiceMap[$module])) {
                $serviceClass = $moduleServiceMap[$module];
                $service = app($serviceClass)->forOffice($office);
                if ($startDate || $endDate) {
                    $service->withDateWindow($startDate, $endDate);
                }
                $service->sync();
            } else {
                throw new Exception("Unknown sync module '{$module}'.");
            }

            // If prune_deleted is requested, run HardDeleteSyncService
            if ($req->prune_deleted) {
                $deleter = app(HardDeleteSyncService::class);
                $tableMap = [
                    'appointment' => 'od_appointments',
                    'appointments' => 'od_appointments',
                    'procedurelog' => 'od_procedure_logs',
                    'procedurelogs' => 'od_procedure_logs',
                    'patient' => 'od_patients',
                    'patients' => 'od_patients',
                    'adjustment' => 'od_adjustments',
                    'adjustments' => 'od_adjustments',
                    'payment' => 'od_payments',
                    'payments' => 'od_payments',
                ];

                $targetTable = $tableMap[$module] ?? 'all';
                $deleter->syncHardDeletes(
                    $targetTable,
                    false,
                    $startDate,
                    $endDate,
                    $office->id ?? 1
                );
            }

            $req->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->info("Completed sync request #{$req->id} successfully.");

        } catch (Exception $e) {
            Log::error("SyncRequest #{$req->id} failed: ".$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $req->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            $this->error("Sync request #{$req->id} failed: ".$e->getMessage());
        }
    }
}
