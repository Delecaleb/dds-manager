<?php

namespace App\Services\Sync;

use RuntimeException;

/**
 * Thrown when a sync run discovers another process has taken over its
 * sync_logs row (its heartbeat went stale). The run must stop without
 * touching the row; the new owner continues from the saved cursor.
 */
class SyncLeaseLostException extends RuntimeException {}
