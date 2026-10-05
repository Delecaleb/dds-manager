<?php

namespace App\Domain\Marketing\GoogleAds;

use RuntimeException;

/** Google rejected a request, or the integration is not set up to make one. */
class GoogleAdsException extends RuntimeException {}
