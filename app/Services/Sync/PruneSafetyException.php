<?php

namespace App\Services\Sync;

use RuntimeException;

/**
 * Thrown when OpenDental reports so many rows missing that a wrong API key or
 * database is more likely than a real deletion. Nothing in that chunk is
 * deleted, and retrying without investigation would give the same answer.
 */
class PruneSafetyException extends RuntimeException {}
