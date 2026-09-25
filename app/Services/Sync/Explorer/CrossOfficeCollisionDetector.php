<?php

namespace App\Services\Sync\Explorer;

use App\Services\Sync\OpenDentalTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finds local rows that were overwritten with another office's data.
 *
 * Every office has its own OpenDental database, so primary keys collide across
 * offices by design (AptNum 4093 exists in each of them). Two offices sharing a
 * primary key AND the exact same change timestamp (DateTStamp, SecDateTEdit…)
 * to the second is not a coincidence: one office's row was written over the
 * other's. Those rows are the candidates handed to RowRepairService, which
 * re-reads each one from its own office's OpenDental.
 *
 * Tables without a change timestamp cannot be checked this way; see
 * detectableColumn().
 */
class CrossOfficeCollisionDetector
{
    /**
     * The change-timestamp column collisions are detected on, or null when the
     * table has none locally.
     */
    public function detectableColumn(OpenDentalTable $table): ?string
    {
        if ($table->serviceClass === null) {
            return null;
        }

        $column = app($table->serviceClass)->describe()['sync_column'] ?? null;

        if ($column === null
            || ! Schema::hasColumn($table->localTable, $column)
            || ! Schema::hasColumn($table->localTable, 'office_id')
            || ! Schema::hasColumn($table->localTable, 'updated_at')) {
            return null;
        }

        return $column;
    }

    /**
     * Suspect primary keys per office, for rows written locally on or after $since.
     *
     * @param  list<int>  $officeIds  restrict the result to these offices (empty = all)
     * @return array<int, list<int>> office_id => primary keys
     */
    public function suspects(OpenDentalTable $table, string $since, array $officeIds = []): array
    {
        $column = $this->detectableColumn($table);

        if ($column === null) {
            return [];
        }

        $pk = $table->primaryKey;

        $collisions = DB::table($table->localTable)
            ->select($pk, $column)
            ->where('updated_at', '>=', $since)
            ->whereNotNull($column)
            ->where($column, 'not like', '0001%')
            ->groupBy($pk, $column)
            ->havingRaw('COUNT(DISTINCT office_id) > 1');

        $rows = DB::table("{$table->localTable} as t")
            ->joinSub($collisions, 'c', fn ($join) => $join->on("t.{$pk}", '=', "c.{$pk}")->on("t.{$column}", '=', "c.{$column}"))
            ->where('t.updated_at', '>=', $since)
            ->when($officeIds !== [], fn ($query) => $query->whereIn('t.office_id', $officeIds))
            ->select('t.office_id', "t.{$pk} as pk")
            ->get();

        $byOffice = [];

        foreach ($rows as $row) {
            $byOffice[(int) $row->office_id][] = (int) $row->pk;
        }

        ksort($byOffice);

        return $byOffice;
    }
}
