<?php

namespace App\Domain\Support;

use Illuminate\Support\Collection;

/**
 * Single source of truth for OpenDental procedure status codes.
 *
 * The synced data encodes ProcStatus inconsistently — both letters ('C', 'TP') and
 * numerics ('2', '1') appear for the same status. Every status comparison in the app
 * routes through here instead of hard-coding a literal, so a change to what "completed"
 * means is a one-place edit.
 *
 * @see refractor-blueprint/03-canonical-definitions.md  (decisions D1, D2)
 */
final class ProcStatus
{
    /** Completed procedures. Blueprint D1. */
    public const COMPLETED = ['C', '2'];

    /** Treatment-planned (proposed) procedures. Blueprint D2. */
    public const TREATMENT_PLANNED = ['TP', '1'];

    /** @return string[] for ->whereIn('ProcStatus', ProcStatus::completed()) */
    public static function completed(): array
    {
        return self::COMPLETED;
    }

    /** @return string[] */
    public static function treatmentPlanned(): array
    {
        return self::TREATMENT_PLANNED;
    }

    /**
     * Quoted, comma-separated status list for inlining into a raw IN (...) clause.
     * e.g. ProcStatus::inList(ProcStatus::COMPLETED) => "'C', '2'"
     */
    public static function inList(array $statuses): string
    {
        return (new Collection($statuses))
            ->map(fn ($s) => "'".addslashes((string) $s)."'")
            ->implode(', ');
    }

    /**
     * Raw SQL fragment: SUM(CASE WHEN {alias}.ProcStatus IN (completed) THEN {col} ELSE 0 END)
     */
    public static function sumWhereCompleted(string $col, string $alias = 'pl'): string
    {
        return self::sumWhere(self::COMPLETED, $col, $alias);
    }

    /**
     * Raw SQL fragment for treatment-planned procedures.
     */
    public static function sumWhereTreatmentPlanned(string $col, string $alias = 'pl'): string
    {
        return self::sumWhere(self::TREATMENT_PLANNED, $col, $alias);
    }

    private static function sumWhere(array $statuses, string $col, string $alias): string
    {
        $list = self::inList($statuses);

        return "SUM(CASE WHEN {$alias}.ProcStatus IN ({$list}) THEN {$col} ELSE 0 END)";
    }
}
