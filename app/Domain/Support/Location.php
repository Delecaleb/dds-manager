<?php

namespace App\Domain\Support;

/**
 * One reportable location.
 *
 * An office is one location. An office that syncs more than one clinic is split so that
 * each clinic is its own location. The key is stable and URL-safe:
 *   "5"   → office 5 as a whole (single-clinic office)
 *   "5:2" → clinic 2 of office 5 (multi-clinic office)
 *
 * Built only by ClinicRegistry, which owns location identity.
 */
final class Location
{
    public function __construct(
        public readonly int $officeId,
        public readonly ?int $clinicNum,
        public readonly string $name,
    ) {}

    public static function keyFor(int $officeId, ?int $clinicNum): string
    {
        return $clinicNum === null ? (string) $officeId : $officeId.':'.$clinicNum;
    }

    public function key(): string
    {
        return self::keyFor($this->officeId, $this->clinicNum);
    }
}
