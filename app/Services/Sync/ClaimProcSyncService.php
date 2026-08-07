<?php

namespace App\Services\Sync;

use App\Models\ClaimProcs;

class ClaimProcSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'claimproc';
    }

    protected function model(): string
    {
        return ClaimProcs::class;
    }

    protected function primaryKey(): string
    {
        return 'ClaimProcNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    protected function dateColumn(): ?string
    {
        return 'ProcDate';
    }

    protected function orderBy(): string
    {
        return 'ClaimProcNum';
    }

    protected function module(): string
    {
        return 'claimprocs';
    }

    protected function transformRow(array $row): array
    {
        $dateCols = ['DateCP', 'ProcDate', 'DateEntry', 'SecDateEntry', 'DateSuppReceived', 'DateInsFinalized'];
        foreach ($dateCols as $col) {
            if (array_key_exists($col, $row)) {
                $row[$col] = $this->normalizeDate($row[$col]);
            }
        }

        if (array_key_exists('SecDateTEdit', $row)) {
            $row['SecDateTEdit'] = $this->normalizeDateTime($row['SecDateTEdit']);
        }

        return $row;
    }
}
