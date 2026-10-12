<?php

namespace App\Services\Sync;

use App\Models\OdOperatory;

/**
 * OpenDental `operatory` → od_operatories. Incremental on DateTStamp; operatories deleted in
 * OpenDental are removed by the hard-delete pruning (HardDeleteSyncService).
 */
class OperatorySyncService extends BaseQuerySyncService
{
    /** OpenDental flag columns, stored as booleans locally. */
    private const FLAGS = ['IsHidden', 'IsHygiene', 'SetProspective', 'IsWebSched', 'IsNewPatAppt'];

    protected function table(): string
    {
        return 'operatory';
    }

    protected function model(): string
    {
        return OdOperatory::class;
    }

    protected function primaryKey(): string
    {
        return 'OperatoryNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    protected function transformRow(array $row): array
    {
        if (array_key_exists('DateTStamp', $row)) {
            $row['DateTStamp'] = $this->normalizeDateTime($row['DateTStamp']);
        }

        // The query API can answer flags as 0/1 or "true"/"false"; store a real boolean.
        foreach (self::FLAGS as $flag) {
            if (array_key_exists($flag, $row)) {
                $row[$flag] = in_array(strtolower(trim((string) $row[$flag])), ['1', 'true'], true) ? 1 : 0;
            }
        }

        return $row;
    }
}
