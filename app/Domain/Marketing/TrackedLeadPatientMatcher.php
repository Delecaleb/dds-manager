<?php

namespace App\Domain\Marketing;

use App\Enums\PatientStatus;
use App\Models\MarketingSiteLocation;
use App\Models\MarketingVisitor;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Credits a lead from a shared website to the location where they are an OpenDental
 * patient: the lead's email or phone matches a patient of exactly one of the site's
 * locations. Run on a schedule (marketing:match-leads), because a lead often becomes a
 * patient days after the signup, and because the public tracking endpoint must never run
 * patient lookups.
 *
 * Only leads a form has not already placed are considered, and only recent ones: a lead
 * that matches no patient within the window stays where it is.
 */
class TrackedLeadPatientMatcher
{
    /** Leads identified within this many days are retried each run. */
    public const WINDOW_DAYS = 60;

    public function __construct(private readonly VisitLocationResolver $locations) {}

    /** @return int leads credited to a location this run */
    public function run(int $days = self::WINDOW_DAYS): int
    {
        $credited = 0;

        MarketingVisitor::query()
            ->whereNotNull('identified_at')
            ->where('identified_at', '>=', now()->subDays($days))
            ->where(fn (Builder $q) => $q->whereNull('location_source')->orWhere('location_source', VisitLocationResolver::SOURCE_PATH))
            ->whereHas('site', fn ($q) => $q->has('locations', '>', 1))
            ->with('site.locations')
            ->chunkById(200, function (Collection $visitors) use (&$credited) {
                foreach ($visitors as $visitor) {
                    $location = $this->patientLocation($visitor);

                    if ($location !== null && $this->locations->credit($visitor, $location, VisitLocationResolver::SOURCE_PATIENT)) {
                        $credited++;
                    }
                }
            });

        return $credited;
    }

    /** The one site location the lead is a patient of, or null when none or several match. */
    public function patientLocation(MarketingVisitor $visitor): ?MarketingSiteLocation
    {
        $siteLocations = $visitor->site->locations;
        $phones = $this->phoneFormats($visitor->phone);
        $email = $visitor->email !== null ? trim($visitor->email) : null;

        if ($siteLocations->isEmpty() || ($phones === [] && ($email === null || $email === ''))) {
            return null;
        }

        $patients = DB::table('od_patients')
            ->whereIn('office_id', $siteLocations->pluck('office_id')->unique()->values())
            ->where(fn (Builder $q) => $q->whereNull('PatStatus')->orWhereNotIn('PatStatus', [(string) PatientStatus::Deleted->value, 'Deleted']))
            ->where(function (Builder $q) use ($email, $phones) {
                if ($email !== null && $email !== '') {
                    $q->orWhere('Email', $email);
                }
                if ($phones !== []) {
                    $q->orWhereIn('WirelessPhone', $phones)->orWhereIn('HmPhone', $phones)->orWhereIn('WkPhone', $phones);
                }
            })
            ->limit(20)
            ->get(['office_id', 'ClinicNum']);

        $matched = $siteLocations->filter(fn (MarketingSiteLocation $l) => $patients->contains(
            fn ($p) => (int) $p->office_id === (int) $l->office_id
                && ($l->clinic_num === null || (int) $p->ClinicNum === (int) $l->clinic_num)
        ));

        return $matched->count() === 1 ? $matched->first() : null;
    }

    /**
     * The ways OpenDental commonly stores a 10-digit US number, so the lookup is a plain
     * equality match instead of stripping punctuation from every patient row.
     *
     * @return list<string>
     */
    public function phoneFormats(?string $phone): array
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return [];
        }

        [$area, $prefix, $line] = [substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6)];

        return [
            "({$area}){$prefix}-{$line}",
            "({$area}) {$prefix}-{$line}",
            "{$area}-{$prefix}-{$line}",
            "{$area}.{$prefix}.{$line}",
            "{$area} {$prefix} {$line}",
            $digits,
            '1'.$digits,
        ];
    }
}
