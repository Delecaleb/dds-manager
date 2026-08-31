<?php

namespace App\Console\Commands;

use App\Services\Sync\HistAppointmentSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Date-bounded historical appointment backfill.
 */
class SyncOpenDentalHistAppointmentsRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:histappointments-range
                            {--since=2025-01-01 : Inclusive AptDateTime lower bound (Y-m-d)}
                            {--until= : Inclusive AptDateTime upper bound (Y-m-d); omit for open-ended "till date"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync historical appointments from OpenDental for an AptDateTime window (defaults to this year / 2025-01-01 onwards)';

    /**
     * Execute the console command.
     */
    public function handle(HistAppointmentSyncService $syncService): int
    {
        try {
            $since = $this->parseDate($this->option('since'), 'since');
            $until = $this->parseDate($this->option('until'), 'until');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($since === null && $until === null) {
            $this->error('Provide at least one of --since / --until; use sync:histappointments for a full-table sync.');

            return self::FAILURE;
        }

        if ($since !== null && $until !== null && $since > $until) {
            $this->error("--since ({$since}) is after --until ({$until}).");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Syncing historical appointments for AptDateTime %s .. %s',
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
