<?php

namespace App\Services\Sync\Explorer;

use App\Models\Office;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\BaseQuerySyncService;
use App\Services\Sync\OpenDentalTable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Repairs individual local rows from the OD Data Explorer.
 *
 * Callers only ever supply a catalog table and primary keys. Row data always
 * comes from OpenDental, and a delete only happens for keys OpenDental has just
 * confirmed are gone — so a stale page, a crafted request or an API outage can
 * never write fabricated data or delete valid rows.
 */
class RowRepairService
{
    public const MAX_KEYS = 5000;

    /** Requests up to this size run immediately; larger ones are queued. */
    public const INLINE_MAX_KEYS = 500;

    private const SUSPICIOUS_DELETE_MINIMUM = 10;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly OpenDentalSqlBuilder $sql,
    ) {}

    /**
     * Re-fetch rows from OpenDental and upsert them locally.
     *
     * @param  list<int|string>  $keys
     * @return array{requested: int, synced: int, not_found: list<int>}
     */
    public function syncFromOpenDental(OpenDentalTable $table, Office $office, array $keys): array
    {
        $this->assertRepairable($table);

        /** @var BaseQuerySyncService $service */
        $service = app($table->serviceClass)->forOffice($office);

        return $service->syncRowsByPrimaryKeys($this->cleanKeys($keys));
    }

    /**
     * Delete local rows that OpenDental confirms no longer exist.
     *
     * If the lookup fails, nothing is deleted (the exception propagates).
     *
     * @param  list<int|string>  $keys
     * @return array{requested: int, deleted: int, still_in_opendental: list<int>}
     */
    public function pruneConfirmedDeleted(OpenDentalTable $table, Office $office, array $keys): array
    {
        $this->assertRepairable($table);

        $keys = $this->cleanKeys($keys);
        $stillInOpenDental = [];
        $confirmedDeleted = [];

        foreach (array_chunk($keys, 500) as $chunk) {
            $rows = $this->queryService->forOffice($office)
                ->shortQuery($this->sql->selectByKeys($table->key, $table->primaryKey, $chunk, $table->primaryKey));

            $present = [];

            foreach ((array) $rows as $row) {
                $row = (array) $row;
                $present[(int) ($row[$table->primaryKey] ?? 0)] = true;
            }

            foreach ($chunk as $key) {
                if (isset($present[$key])) {
                    $stillInOpenDental[] = $key;
                } else {
                    $confirmedDeleted[] = $key;
                }
            }
        }

        // Same circuit breaker as reconciliation: OpenDental "missing" every one of
        // many rows points at a wrong connection, not a mass deletion.
        if (count($confirmedDeleted) >= self::SUSPICIOUS_DELETE_MINIMUM && count($confirmedDeleted) === count($keys)) {
            throw new RuntimeException('OpenDental returned none of the '.count($keys).' records. Nothing was deleted — check this office\'s API connection.');
        }

        $deleted = 0;

        foreach (array_chunk($confirmedDeleted, 500) as $chunk) {
            $deleted += DB::table($table->localTable)
                ->where('office_id', (int) $office->id)
                ->whereIn($table->primaryKey, $chunk)
                ->delete();
        }

        return [
            'requested' => count($keys),
            'deleted' => $deleted,
            'still_in_opendental' => $stillInOpenDental,
        ];
    }

    private function assertRepairable(OpenDentalTable $table): void
    {
        if (! $table->isRepairable()) {
            throw new InvalidArgumentException("'{$table->key}' cannot be repaired from OpenDental.");
        }
    }

    /**
     * @param  list<int|string>  $keys
     * @return list<int>
     */
    private function cleanKeys(array $keys): array
    {
        $clean = array_values(array_unique(array_filter(array_map('intval', $keys), fn (int $key) => $key > 0)));

        if ($clean === []) {
            throw new InvalidArgumentException('No valid record keys provided.');
        }

        if (count($clean) > self::MAX_KEYS) {
            throw new InvalidArgumentException('Too many records in one request (max '.self::MAX_KEYS.').');
        }

        return $clean;
    }
}
