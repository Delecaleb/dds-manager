<?php

namespace App\Jobs;

use App\Models\Office;
use App\Services\Sync\Explorer\RowRepairService;
use App\Services\Sync\OpenDentalTableCatalog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use InvalidArgumentException;

/**
 * Explorer repair action (sync rows from OpenDental, or prune rows OpenDental
 * confirms deleted). Small requests run inline via dispatchSync; large ones
 * run on the sync queue so they never depend on the web request surviving.
 */
class RepairOpenDentalRows implements ShouldQueue
{
    use Queueable;

    public const ACTION_SYNC = 'sync';

    public const ACTION_PRUNE = 'prune';

    public int $tries = 3;

    public int $timeout;

    /**
     * @param  list<int>  $keys
     */
    public function __construct(
        public int $officeId,
        public string $table,
        public string $action,
        public array $keys,
    ) {
        $this->timeout = (int) config('sync.queue.job_timeout', 600);

        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.name'));
    }

    /**
     * @return array{requested: int, synced?: int, not_found?: list<int>, deleted?: int, still_in_opendental?: list<int>}
     */
    public function handle(RowRepairService $repair, OpenDentalTableCatalog $catalog): array
    {
        $office = Office::findOrFail($this->officeId);
        $table = $catalog->resolve($this->table) ?? throw new InvalidArgumentException("Unknown table '{$this->table}'.");

        return match ($this->action) {
            self::ACTION_SYNC => $repair->syncFromOpenDental($table, $office, $this->keys),
            self::ACTION_PRUNE => $repair->pruneConfirmedDeleted($table, $office, $this->keys),
        };
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }
}
