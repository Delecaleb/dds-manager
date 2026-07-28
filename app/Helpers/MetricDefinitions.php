<?php

namespace App\Helpers;

use Illuminate\Database\Query\Expression;

class MetricDefinitions
{
    /**
     * Definition for unique patient visits count (unique combination of PatNum and Date).
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function patientVisits(?string $alias = null): string
    {
        $raw = 'COUNT(DISTINCT PatNum, DATE(ProcDate))';

        return $raw.($alias ? " AS {$alias}" : '');
    }

    /**
     * Definition for unique scheduled patients (unique combination of PatNum and Date).
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function scheduledPatients(?string $alias = null): string
    {
        $raw = 'COUNT(*)';

        return $raw.($alias ? " AS {$alias}" : '');
    }

    /**
     * Definition for gross production (sum of procedure fees).
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function grossProduction(?string $alias = null): string
    {
        $raw = 'SUM(ProcFee)';

        return $raw.($alias ? " AS {$alias}" : '');
    }

    /**
     * Definition for adjustment sum.
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function adjustments(?string $alias = null): string
    {
        $raw = 'SUM(AdjAmt)';

        return $raw.($alias ? " AS {$alias}" : '');
    }

    /**
     * Definition for writeoffs sum.
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function writeOffs(?string $alias = null): string
    {
        $raw = 'SUM(WriteOff)';

        return $raw.($alias ? " AS {$alias}" : '');
    }

    /**
     * Definition for collections.
     *
     * @param  string|null  $alias  Optional AS alias.
     * @return Expression
     */
    public static function collections(?string $alias = null): string
    {
        $raw = 'SUM(SplitAmt)';

        return $raw.($alias ? " AS {$alias}" : '');
    }
}
