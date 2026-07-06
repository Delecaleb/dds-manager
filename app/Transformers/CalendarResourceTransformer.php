<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Collection;

class CalendarResourceTransformer
{
    /**
     * Parse the day's appointments and isolate unique Operatories into headers.
     */
    public static function transform(Collection $appointments): array
    {
        if ($appointments->isEmpty()) {
            return [];
        }

        return $appointments
            ->groupBy('Op')
            ->map(function ($apts, $op) {
                // Use the first appointment in this operatory to glean the primary provider abbreviation loosely
                $first = $apts->first();
                $provAbbr = trim($first->provider?->Abbr ?? '');

                return [
                    'id' => 'op-' . $op,
                    'title' => $provAbbr ?: ('Op ' . $op),
                ];
            })
            ->sortKeys()
            ->values()
            ->toArray();
    }
}
