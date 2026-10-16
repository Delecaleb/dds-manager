<?php

namespace App\Services\Sync\Concerns;

use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Database\QueryException;

/**
 * Retry a database write when MySQL reports a deadlock or lock wait timeout. Offices share
 * the synced tables and their jobs run in parallel, so concurrent writes can briefly collide;
 * MySQL then cancels one of them, and running it again almost always succeeds.
 */
trait RetriesLockContention
{
    use DetectsConcurrencyErrors;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function retryOnLockContention(callable $callback): mixed
    {
        $maxAttempts = max(1, (int) config('sync.db_lock_attempts', 3));

        for ($attempt = 1; ; $attempt++) {
            try {
                return $callback();
            } catch (QueryException $e) {
                if ($attempt >= $maxAttempts || ! $this->causedByConcurrencyError($e)) {
                    throw $e;
                }

                usleep(random_int(100, 400) * 1000 * $attempt);
            }
        }
    }
}
