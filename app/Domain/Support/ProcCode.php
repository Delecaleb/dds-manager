<?php

namespace App\Domain\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for OpenDental procedure codes and exclusions.
 */
final class ProcCode
{
    /** Broken / missed / cancelled appointment ADA codes. */
    public const BROKEN_APPOINTMENT_CODES = ['D9986', 'D9987'];

    /**
     * Cache for resolved CodeNums per officeId.
     *
     * @var array<string, array<string>>
     */
    private static array $resolvedBrokenCodesCache = [];

    /**
     * Get list of CodeNum values for broken / missed / cancelled appointment procedures
     * for a given office (or all offices).
     *
     * @return array<string>
     */
    public static function brokenAppointmentCodeNums(?int $officeId = null): array
    {
        $cacheKey = $officeId !== null ? (string) $officeId : 'all';

        if (isset(self::$resolvedBrokenCodesCache[$cacheKey])) {
            return self::$resolvedBrokenCodesCache[$cacheKey];
        }

        $codeNums = [];

        if (Schema::hasTable('od_procedures')) {
            $codeNums = DB::table('od_procedures')
                ->when($officeId !== null, fn ($q) => $q->where('office_id', $officeId))
                ->whereIn('ProcCode', self::BROKEN_APPOINTMENT_CODES)
                ->pluck('CodeNum')
                ->map(fn ($c) => (string) $c)
                ->filter()
                ->all();
        }

        // Always include legacy fallback ID '626'
        $codeNums[] = '626';

        $result = array_values(array_unique($codeNums));
        self::$resolvedBrokenCodesCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Clear the in-memory cache (useful for tests).
     */
    public static function clearCache(): void
    {
        self::$resolvedBrokenCodesCache = [];
    }

    /**
     * Quoted, comma-separated CodeNum list for inlining into a raw IN / NOT IN (...) clause.
     */
    public static function brokenAppointmentCodeNumsInList(?int $officeId = null): string
    {
        $nums = self::brokenAppointmentCodeNums($officeId);

        return "'".implode("', '", array_map('addslashes', $nums))."'";
    }

    /**
     * Raw SQL condition fragment: COALESCE({alias}.CodeNum, '') NOT IN ('626', ...)
     */
    public static function notBrokenAppointmentSql(string $alias = 'pl', ?int $officeId = null): string
    {
        $list = self::brokenAppointmentCodeNumsInList($officeId);
        $col = $alias !== '' ? "{$alias}.CodeNum" : 'CodeNum';

        return "COALESCE({$col}, '') NOT IN ({$list})";
    }
}
