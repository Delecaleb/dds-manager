<?php

namespace App\Domain\Marketing\MetaAds;

use RuntimeException;

/**
 * Meta rejected a request, or the integration is not set up to make one. $tokenRejected
 * is true when the token itself is no longer accepted (revoked, expired, or the system
 * user lost access), which needs a new token rather than a retry.
 */
class MetaAdsException extends RuntimeException
{
    public function __construct(string $message, public readonly bool $tokenRejected = false)
    {
        parent::__construct($message);
    }
}
