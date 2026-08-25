<?php

namespace App\Services\Sync;

use App\Models\OdPatient;

class PatientSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'patient';
    }

    protected function model(): string
    {
        return OdPatient::class;
    }

    protected function primaryKey(): string
    {
        return 'PatNum';
    }

    /**
     * patient table supports incremental sync
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Business date a windowed sync (sync:patients-range) filters on.
     * SecDateEntry is when the patient was entered into Open Dental.
     */
    protected function dateColumn(): ?string
    {
        return 'SecDateEntry';
    }

    /**
     * Optional.
     * Helps performance when selecting *
     */
    protected function orderBy(): string
    {
        return 'PatNum';
    }

    /**
     * Optional.
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }

    protected function transformRow(array $row): array
    {
        $dateCols = ['Birthdate', 'SecDateEntry', 'DateFirstVisit', 'DateTimeDeceased', 'AdmitDate'];
        foreach ($dateCols as $col) {
            if (array_key_exists($col, $row)) {
                $row[$col] = $this->normalizeDate($row[$col]);
            }
        }

        if (array_key_exists('DateTStamp', $row)) {
            $row['DateTStamp'] = $this->normalizeDateTime($row['DateTStamp']);
        }

        return $row;
    }
}
