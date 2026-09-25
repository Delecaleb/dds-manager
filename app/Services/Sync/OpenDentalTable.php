<?php

namespace App\Services\Sync;

/**
 * Metadata for one OpenDental table and its local synced copy.
 *
 * Built by OpenDentalTableCatalog from the sync services themselves, so the
 * table name, primary key and date column can never drift from what the
 * sync actually does.
 */
final class OpenDentalTable
{
    /**
     * @param  list<string>  $compareColumns  columns checked for value drift during reconciliation
     */
    public function __construct(
        public readonly string $key,
        public readonly string $localTable,
        public readonly string $primaryKey,
        public readonly ?string $dateColumn,
        public readonly array $compareColumns,
        public readonly ?string $module,
        public readonly ?string $serviceClass,
        public readonly bool $existsInOpenDental,
    ) {}

    /**
     * Whether rows can be re-fetched from OpenDental and repaired locally.
     */
    public function isRepairable(): bool
    {
        return $this->existsInOpenDental && $this->serviceClass !== null;
    }
}
