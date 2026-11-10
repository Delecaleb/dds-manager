<?php

namespace App\Domain\Production;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

/** OpenDental's own procedure code list: each code's plain description and category. */
class ProcedureCatalogService
{
    /** @param  list<string>  $codes  empty = every code */
    public function paginate(int $officeId, array $codes, int $perPage): CursorPaginator
    {
        return DB::table('od_procedures as pc')
            ->leftJoin('od_definitions as d', function ($join) {
                $join->on('pc.ProcCat', '=', 'd.DefNum')->on('pc.office_id', '=', 'd.office_id');
            })
            ->where('pc.office_id', $officeId)
            ->when($codes !== [], fn ($q) => $q->whereIn('pc.ProcCode', $codes))
            ->select('pc.CodeNum', 'pc.ProcCode', 'pc.Descript', 'pc.AbbrDesc', 'pc.IsHygiene', 'd.ItemName as category')
            ->orderBy('pc.ProcCode')
            ->orderBy('pc.CodeNum')
            ->cursorPaginate($perPage);
    }
}
