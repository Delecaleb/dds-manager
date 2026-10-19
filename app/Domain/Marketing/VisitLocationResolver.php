<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingEvent;
use App\Models\MarketingSession;
use App\Models\MarketingSite;
use App\Models\MarketingSiteLocation;
use App\Models\MarketingVisitor;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Which location a tracked visit and visitor are credited to: the one definition, used live
 * by TrackingIngestService, by the OpenDental patient match, and to re-credit a site's
 * history when its locations or rules change.
 *
 * A site serving one location credits everything to it ('site'). On a shared site, one
 * script serves every location, so the location is read off what the visitor does,
 * strongest clue first:
 *
 *   form    — the signup form named the location the patient chose;
 *   patient — the lead's phone or email is that location's OpenDental patient;
 *   path    — the visit viewed a page under the location's paths (e.g. /grand-rapids).
 *
 * A form or patient credit is about the person, so it carries all their visits with it,
 * past and future, over any weaker clue. A path credit is about one visit (the first page
 * that matched decides it), and an undecided person takes it from their first such visit.
 * A visit with no clue stays undecided (NULL): it counts once in the all-locations view and
 * in no single location, so no location is credited with traffic it did not earn.
 */
final class VisitLocationResolver
{
    public const SOURCE_SITE = 'site';

    public const SOURCE_PATH = 'path';

    public const SOURCE_PATIENT = 'patient';

    public const SOURCE_FORM = 'form';

    /** Stronger clues replace weaker ones, never the reverse. */
    private const RANK = [self::SOURCE_PATH => 1, self::SOURCE_PATIENT => 2, self::SOURCE_FORM => 3, self::SOURCE_SITE => 4];

    public function __construct(private readonly ClinicRegistry $clinics) {}

    /**
     * The location for a visitor or visit being created: the site's own on a one-location
     * site; on a shared site, the person's if a form or patient match already decided it.
     *
     * @return array{office_id: ?int, clinic_num: ?int, location_source: ?string}
     */
    public function initial(MarketingSite $site, ?MarketingVisitor $visitor = null): array
    {
        $locations = $site->locations;

        if ($locations->count() === 1) {
            return $this->stamp($locations->first(), self::SOURCE_SITE);
        }

        if ($visitor !== null && in_array($visitor->location_source, [self::SOURCE_FORM, self::SOURCE_PATIENT], true)) {
            return [
                'office_id' => $visitor->office_id,
                'clinic_num' => $visitor->clinic_num,
                'location_source' => $visitor->location_source,
            ];
        }

        return $this->stamp(null, null);
    }

    /** A page view on a shared site: the first page under a location's paths decides an undecided visit. */
    public function onPageview(MarketingSite $site, MarketingVisitor $visitor, MarketingSession $session, ?string $path): void
    {
        if (! $site->isShared() || $session->location_source !== null) {
            return;
        }

        $location = $this->matchPath($site, $path);

        if ($location === null) {
            return;
        }

        $session->forceFill($this->stamp($location, self::SOURCE_PATH))->save();

        if ($visitor->location_source === null) {
            $visitor->forceFill($this->stamp($location, self::SOURCE_PATH))->save();
        }
    }

    /** A signup on a shared site that names a location (`location: 'grand-rapids'`). */
    public function onSignup(MarketingSite $site, MarketingVisitor $visitor, mixed $value): void
    {
        if (! $site->isShared() || ! is_scalar($value)) {
            return;
        }

        $location = $this->matchCode($site, (string) $value);

        if ($location !== null) {
            $this->credit($visitor, $location, self::SOURCE_FORM);
        }
    }

    /**
     * Credit a person to a location, with every visit of theirs that a weaker clue (or
     * none) decided. Returns false when a stronger clue already decided them.
     */
    public function credit(MarketingVisitor $visitor, MarketingSiteLocation $location, string $source): bool
    {
        if ($this->rank($visitor->location_source) > $this->rank($source)) {
            return false;
        }

        $visitor->forceFill($this->stamp($location, $source))->save();
        $this->carryVisits($visitor);

        return true;
    }

    /** The site location whose paths cover this page, if any. */
    public function matchPath(MarketingSite $site, ?string $path): ?MarketingSiteLocation
    {
        return $site->locations->first(fn (MarketingSiteLocation $l) => $l->coversPath($path));
    }

    /** The site location a signup form's value names: its code, its name, or its key ("8:2"). */
    public function matchCode(MarketingSite $site, string $value): ?MarketingSiteLocation
    {
        $value = trim($value);
        $code = MarketingSiteLocation::normalizeCode($value);

        if ($value === '') {
            return null;
        }

        return $site->locations->first(fn (MarketingSiteLocation $l) => $l->locationKey() === $value
            || ($code !== '' && in_array($code, [$l->formCode($this->nameOf($l)), MarketingSiteLocation::normalizeCode($this->nameOf($l))], true)));
    }

    /**
     * Location name => the value its signup form sends, for the install instructions.
     *
     * @return array<string, string>
     */
    public function formCodes(MarketingSite $site): array
    {
        $codes = [];
        foreach ($site->locations as $location) {
            $codes[$this->nameOf($location)] = $location->formCode($this->nameOf($location));
        }

        return $codes;
    }

    public function nameOf(MarketingSiteLocation $location): string
    {
        return $this->clinics->labelFor((int) $location->office_id, $location->clinic_num === null ? null : (int) $location->clinic_num);
    }

    /**
     * Re-credit a site's whole history after its locations or rules changed, by the same
     * rules ingest applies live. Safe to run any number of times.
     */
    public function restamp(MarketingSite $site): void
    {
        $site->load('locations');
        $locations = $site->locations;

        if ($locations->count() <= 1) {
            $stamp = $this->stamp($locations->first(), $locations->isEmpty() ? null : self::SOURCE_SITE);

            foreach (['marketing_visitors', 'marketing_sessions'] as $table) {
                DB::table($table)->where('site_id', $site->id)->update($stamp);
            }

            return;
        }

        // 1. Path and one-location credits are recomputed below; a form or patient credit
        //    stands while its location is still one of the site's.
        foreach (['marketing_visitors', 'marketing_sessions'] as $table) {
            DB::table($table)->where('site_id', $site->id)
                ->where(fn (Builder $q) => $q->whereIn('location_source', [self::SOURCE_SITE, self::SOURCE_PATH])
                    ->orWhere(fn (Builder $w) => $w->whereNotNull('location_source')
                        ->whereNot(fn (Builder $n) => $this->whereOneOf($n, $locations))))
                ->update($this->stamp(null, null));
        }

        // 2. Each undecided visit: the first page view under a location's paths decides it.
        if ($locations->contains(fn (MarketingSiteLocation $l) => ! empty($l->path_prefixes))) {
            MarketingSession::where('site_id', $site->id)->whereNull('location_source')->select('id')
                ->chunkById(500, function (Collection $sessions) use ($site) {
                    $pages = DB::table('marketing_events')
                        ->whereIn('session_id', $sessions->pluck('id'))
                        ->where('type', MarketingEvent::TYPE_PAGEVIEW)
                        ->orderBy('occurred_at')->orderBy('id')
                        ->get(['session_id', 'path'])
                        ->groupBy('session_id');

                    foreach ($pages as $sessionId => $views) {
                        foreach ($views as $view) {
                            if (($location = $this->matchPath($site, $view->path)) !== null) {
                                DB::table('marketing_sessions')->where('id', $sessionId)->update($this->stamp($location, self::SOURCE_PATH));
                                break;
                            }
                        }
                    }
                });

            // 3. An undecided person takes the location of their first path-decided visit.
            MarketingVisitor::where('site_id', $site->id)->whereNull('location_source')->select('id')
                ->chunkById(500, function (Collection $visitors) {
                    DB::table('marketing_sessions')
                        ->whereIn('visitor_id', $visitors->pluck('id'))
                        ->where('location_source', self::SOURCE_PATH)
                        ->orderBy('started_at')->orderBy('id')
                        ->get(['visitor_id', 'office_id', 'clinic_num'])
                        ->unique('visitor_id')
                        ->each(fn ($first) => DB::table('marketing_visitors')->where('id', $first->visitor_id)->update([
                            'office_id' => $first->office_id,
                            'clinic_num' => $first->clinic_num,
                            'location_source' => self::SOURCE_PATH,
                        ]));
                });
        }

        // 4. A person decided by form or patient carries their visits with them.
        MarketingVisitor::where('site_id', $site->id)
            ->whereIn('location_source', [self::SOURCE_FORM, self::SOURCE_PATIENT])
            ->chunkById(500, fn (Collection $visitors) => $visitors->each(fn (MarketingVisitor $v) => $this->carryVisits($v)));
    }

    /** Give the person's location to each of their visits that a weaker clue (or none) decided. */
    private function carryVisits(MarketingVisitor $visitor): void
    {
        $weaker = array_keys(array_filter(self::RANK, fn (int $rank) => $rank < $this->rank($visitor->location_source)));

        MarketingSession::where('visitor_id', $visitor->id)
            ->where(fn (Builder $q) => $q->whereNull('location_source')->orWhereIn('location_source', $weaker))
            ->update([
                'office_id' => $visitor->office_id,
                'clinic_num' => $visitor->clinic_num,
                'location_source' => $visitor->location_source,
            ]);
    }

    /** @param Collection<int, MarketingSiteLocation> $locations */
    private function whereOneOf(Builder $query, Collection $locations): void
    {
        foreach ($locations as $location) {
            $query->orWhere(fn (Builder $w) => $w->where('office_id', $location->office_id)
                ->where(fn (Builder $c) => $location->clinic_num === null
                    ? $c->whereNull('clinic_num')
                    : $c->where('clinic_num', $location->clinic_num)));
        }
    }

    private function rank(?string $source): int
    {
        return self::RANK[$source] ?? 0;
    }

    /** @return array{office_id: ?int, clinic_num: ?int, location_source: ?string} */
    private function stamp(?MarketingSiteLocation $location, ?string $source): array
    {
        return [
            'office_id' => $location?->office_id === null ? null : (int) $location->office_id,
            'clinic_num' => $location?->clinic_num === null ? null : (int) $location->clinic_num,
            'location_source' => $location === null ? null : $source,
        ];
    }
}
