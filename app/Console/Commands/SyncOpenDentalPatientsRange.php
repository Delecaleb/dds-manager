<?php

namespace App\Console\Commands;

use App\Services\Sync\PatientSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Date-bounded patient backfill.
 *
 * Reuses PatientSyncService (same batching, retry, keyset cursor and
 * idempotent upserts as sync:patients) and only narrows the source query
 * to a SecDateEntry window. It records progress under its own sync_logs module
 * ("patient:<start>..<end>"), so it can be killed and resumed, and can
 * never clobber the cursor or watermark of the full-table sync.
 */
class SyncOpenDentalPatientsRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patients-range
                            {--since=2025-01-01 : Inclusive SecDateEntry lower bound (Y-m-d)}
                            {--until= : Inclusive SecDateEntry upper bound (Y-m-d); omit for open-ended "till date"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync patients from OpenDental for a SecDateEntry window (defaults to 2025-01-01 onwards)';

    /**
     * Execute the console command.
     */
    public function handle(PatientSyncService $syncService): int
    {
        try {
            $since = $this->parseDate($this->option('since'), 'since');
            $until = $this->parseDate($this->option('until'), 'until');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($since === null && $until === null) {
            $this->error('Provide at least one of --since / --until; use sync:patients for a full-table sync.');

            return self::FAILURE;
        }

        if ($since !== null && $until !== null && $since > $until) {
            $this->error("--since ({$since}) is after --until ({$until}).");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Syncing patient for SecDateEntry %s .. %s',
            $since ?? 'beginning',
            $until ?? 'today'
        ));

        try {
            $syncService->withDateWindow($since, $until)->sync();
        } catch (\Throwable $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Normalize a CLI date option to 'Y-m-d', or null when it was omitted.
     *
     * @throws \InvalidArgumentException when the value is present but unparseable
     */
    private function parseDate(?string $value, string $name): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("--{$name} is not a valid date: {$value}");
        }
    }
}
