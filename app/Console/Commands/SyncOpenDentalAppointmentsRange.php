<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\AppointmentSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Date-bounded appointment backfill.
 *
 * Reuses AppointmentSyncService (same batching, retry, keyset cursor and
 * idempotent upserts as sync:appointments) and only narrows the source query
 * to an AptDateTime window. It records progress under its own sync_logs module
 * ("office_<id>:appointment:<start>..<end>"), so it can be killed and resumed, and can
 * never clobber the cursor or watermark of the full-table sync.
 */
class SyncOpenDentalAppointmentsRange extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:appointments-range
                            {--since=2025-01-01 : Inclusive AptDateTime lower bound (Y-m-d)}
                            {--until= : Inclusive AptDateTime upper bound (Y-m-d); omit for open-ended "till date"}
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync appointments from OpenDental for an AptDateTime window (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(AppointmentSyncService $syncService): int
    {
        try {
            $since = $this->parseDate($this->option('since'), 'since');
            $until = $this->parseDate($this->option('until'), 'until');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($since === null && $until === null) {
            $this->error('Provide at least one of --since / --until; use sync:appointments for a full-table sync.');

            return self::FAILURE;
        }

        if ($since !== null && $until !== null && $since > $until) {
            $this->error("--since ({$since}) is after --until ({$until}).");

            return self::FAILURE;
        }

        $label = sprintf('appointments (%s .. %s)', $since ?? 'beginning', $until ?? 'today');

        return $this->syncEachOffice($label, function ($office) use ($since, $until) {
            app(AppointmentSyncService::class)
                ->forOffice($office)
                ->withDateWindow($since, $until)
                ->sync();
        });
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
