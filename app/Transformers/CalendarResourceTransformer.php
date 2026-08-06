<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Collection;

class CalendarResourceTransformer
{
    /**
     * Map the required 10 operatories in order, optionally filtering only active ones.
     */
    public static function transform(Collection $appointments, bool $activeOnly = false): array
    {
        $staticOpsOrder = ['2', '3', '4', '6', '7', '1', '5', '8', '9', '10'];

        $staticOps = [
            '2' => ['id' => 'op-2', 'title' => 'DR-2'],
            '3' => ['id' => 'op-3', 'title' => 'DR-3'],
            '4' => ['id' => 'op-4', 'title' => 'DR-4'],
            '6' => ['id' => 'op-6', 'title' => 'Unassigned 6'],
            '7' => ['id' => 'op-7', 'title' => 'Unassigned 7'],
            '1' => ['id' => 'op-1', 'title' => 'DR-1'],
            '5' => ['id' => 'op-5', 'title' => 'DR-5'],
            '8' => ['id' => 'op-8', 'title' => 'Unassigned 8'],
            '9' => ['id' => 'op-9', 'title' => 'Unassigned 9'],
            '10' => ['id' => 'op-10', 'title' => 'Unassigned 10'],
        ];

        if ($activeOnly) {
            $activeOps = $appointments->pluck('Op')->unique()->map(fn ($val) => (string) $val)->toArray();

            $filtered = [];
            foreach ($staticOpsOrder as $opKey) {
                if (in_array($opKey, $activeOps)) {
                    $filtered[] = $staticOps[$opKey];
                }
            }

            return empty($filtered) ? array_values($staticOps) : $filtered;
        }

        $result = [];
        foreach ($staticOpsOrder as $opKey) {
            $result[] = $staticOps[$opKey];
        }

        return $result;
    }
}
