<?php

namespace App\Domain\DeveloperApi;

use App\Models\ApiRequestLog;
use Carbon\CarbonImmutable;

/**
 * Developer API usage over time, from api_request_logs: calls per day, split into those
 * that succeeded and those refused or failed (HTTP 400 and above, including rate-limited).
 * Days are calendar days in the app timezone; days with no calls are zero, not missing.
 */
class ApiUsageService
{
    /** The periods the usage charts offer, in days. */
    public const PERIODS = [7, 30, 90];

    public const DEFAULT_PERIOD = 30;

    /** A requested period (e.g. ?days=), or the default when it is not one on offer. */
    public static function period(mixed $days): int
    {
        return in_array((int) $days, self::PERIODS, true) ? (int) $days : self::DEFAULT_PERIOD;
    }

    private const SUCCESS_COLOR = '#2563eb';

    private const FAILED_COLOR = '#e11d48';

    /**
     * Daily calls for the given keys (null = every key), as DDS.chart data plus totals.
     *
     * @param  list<int>|null  $keyIds
     * @return array{labels: list<string>, series: list<array{name: string, color: string, data: list<int>}>, total: int, failed: int}
     */
    public function daily(?array $keyIds, int $days): array
    {
        $days = self::period($days);
        $end = CarbonImmutable::today();
        $start = $end->subDays($days - 1);

        $rows = [];
        if ($keyIds !== []) {
            $rows = ApiRequestLog::query()
                ->when($keyIds !== null, fn ($query) => $query->whereIn('api_key_id', $keyIds))
                ->where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as calls, SUM(CASE WHEN status >= 400 THEN 1 ELSE 0 END) as failed')
                ->groupByRaw('DATE(created_at)')
                ->get()
                ->keyBy(fn ($row) => (string) $row->day)
                ->all();
        }

        $labels = $succeeded = $failed = [];
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $row = $rows[$day->toDateString()] ?? null;
            $calls = (int) ($row->calls ?? 0);
            $errors = (int) ($row->failed ?? 0);

            $labels[] = $day->format('M j');
            $succeeded[] = $calls - $errors;
            $failed[] = $errors;
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Successful calls', 'color' => self::SUCCESS_COLOR, 'data' => $succeeded],
                ['name' => 'Failed or refused', 'color' => self::FAILED_COLOR, 'data' => $failed],
            ],
            'total' => array_sum($succeeded) + array_sum($failed),
            'failed' => array_sum($failed),
        ];
    }
}
