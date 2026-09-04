<?php

namespace App\Transformers;

use App\Models\Office;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalendarResourceTransformer
{
    /**
     * Map the required 10 operatories in order, optionally filtering only active ones.
     */
    public static function transform(Collection $appointments, bool $activeOnly = false): array
    {
        $officeId = Office::getActiveOfficeId();

        $dbOps = collect();
        if (Schema::hasTable('od_operatories')) {
            $dbOps = DB::table('od_operatories')
                ->where('office_id', $officeId)
                ->where(function ($q) {
                    $q->whereNull('IsHidden')->orWhereIn('IsHidden', ['false', '0', 0, false]);
                })
                ->orderBy('ItemOrder')
                ->get();
        }

        $resources = [];
        if ($dbOps->isNotEmpty()) {
            foreach ($dbOps as $op) {
                $resources[] = [
                    'id' => 'op-'.$op->OperatoryNum,
                    'title' => $op->OpName ?: ('Op '.$op->OperatoryNum),
                    'op_num' => (string) $op->OperatoryNum,
                ];
            }
        } else {
            $staticOpsOrder = ['2', '3', '4', '6', '7', '1', '5', '8', '9', '10'];
            $staticOps = [
                '2' => ['id' => 'op-2', 'title' => 'DR-2', 'op_num' => '2'],
                '3' => ['id' => 'op-3', 'title' => 'DR-3', 'op_num' => '3'],
                '4' => ['id' => 'op-4', 'title' => 'DR-4', 'op_num' => '4'],
                '6' => ['id' => 'op-6', 'title' => 'Unassigned 6', 'op_num' => '6'],
                '7' => ['id' => 'op-7', 'title' => 'Unassigned 7', 'op_num' => '7'],
                '1' => ['id' => 'op-1', 'title' => 'DR-1', 'op_num' => '1'],
                '5' => ['id' => 'op-5', 'title' => 'DR-5', 'op_num' => '5'],
                '8' => ['id' => 'op-8', 'title' => 'Unassigned 8', 'op_num' => '8'],
                '9' => ['id' => 'op-9', 'title' => 'Unassigned 9', 'op_num' => '9'],
                '10' => ['id' => 'op-10', 'title' => 'Unassigned 10', 'op_num' => '10'],
            ];
            foreach ($staticOpsOrder as $opKey) {
                $resources[] = $staticOps[$opKey];
            }
        }

        if ($activeOnly) {
            $activeOps = $appointments->pluck('Op')->unique()->map(fn ($val) => (string) $val)->toArray();
            $filtered = array_values(array_filter($resources, fn ($r) => in_array($r['op_num'], $activeOps)));

            return empty($filtered) ? array_values($resources) : $filtered;
        }

        return array_values($resources);
    }
}
