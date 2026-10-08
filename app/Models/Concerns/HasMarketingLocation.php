<?php

namespace App\Models\Concerns;

use App\Domain\Support\Location;

/**
 * A Growth Engine row that belongs to one reporting location (office_id + clinic_num),
 * keyed exactly as ClinicRegistry keys locations ("5", "5:2").
 */
trait HasMarketingLocation
{
    /** The location key this row is assigned to, or null when unassigned. */
    public function locationKey(): ?string
    {
        if ($this->office_id === null) {
            return null;
        }

        return Location::keyFor((int) $this->office_id, $this->clinic_num === null ? null : (int) $this->clinic_num);
    }

    public function assignLocation(?Location $location): static
    {
        $this->office_id = $location?->officeId;
        $this->clinic_num = $location?->clinicNum;

        return $this;
    }

    /** Take the location of another located row (an account inheriting its connection's). */
    public function inheritLocationFrom(object $owner): static
    {
        $this->office_id = $owner->office_id;
        $this->clinic_num = $owner->clinic_num;

        return $this;
    }
}
