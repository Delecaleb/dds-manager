<?php

namespace App\Domain\SiteBuilder;

use RuntimeException;

/** The model answered, but not with usable site content (cut off, declined, bad JSON). */
class SiteGenerationException extends RuntimeException {}
