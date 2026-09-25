<?php

namespace App\Domain\Marketing;

use App\Models\MarketingEvent;
use App\Models\MarketingSession;
use App\Models\MarketingSite;
use App\Models\MarketingVisitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns one beacon from the tracking snippet into visitor, session and event rows.
 *
 * This is the only writer of tracking data, and the only place attribution is read off a
 * URL, so "what counts as paid search" is decided once.
 *
 * The endpoint that calls this is public and unauthenticated — anyone who reads a page's
 * source can post to it. Nothing here trusts the payload: the site key must exist, strings
 * are truncated to their column widths, and ids the caller supplies are only ever used to
 * look up rows that already belong to that site.
 */
class TrackingIngestService
{
    /** Query parameters that name the ad that paid for the click. */
    private const CLICK_IDS = [
        'gclid' => 'google',
        'wbraid' => 'google',
        'gbraid' => 'google',
        'fbclid' => 'meta',
        'msclkid' => 'microsoft',
        'ttclid' => 'tiktok',
    ];

    /** Hosts we recognise as search engines, for the organic/referral split. */
    private const SEARCH_HOSTS = ['google', 'bing', 'yahoo', 'duckduckgo', 'ecosia', 'baidu', 'yandex'];

    /** Hosts we recognise as social networks. */
    private const SOCIAL_HOSTS = ['facebook', 'instagram', 'linkedin', 'twitter', 'x.com', 'tiktok', 'pinterest', 'youtube', 'reddit'];

    /**
     * Record one event.
     *
     * @param  array<string, mixed>  $payload  the beacon body
     * @return array{visitor_uid: string, session_uid: string}|null ids for the browser to keep, or null when rejected
     */
    public function record(array $payload, ?string $userAgent = null): ?array
    {
        $site = $this->site((string) ($payload['k'] ?? ''));

        if ($site === null) {
            return null;
        }

        $type = $this->type((string) ($payload['t'] ?? MarketingEvent::TYPE_PAGEVIEW));
        $url = (string) ($payload['url'] ?? '');
        $referrer = (string) ($payload['ref'] ?? '');
        $now = Carbon::now();

        $attribution = $this->attribution($url, $referrer, $site->domain);
        $visitorUid = $this->uuid($payload['v'] ?? null);
        $sessionUid = $this->uuid($payload['s'] ?? null);

        return DB::transaction(function () use ($site, $type, $url, $referrer, $now, $attribution, $visitorUid, $sessionUid, $payload, $userAgent) {
            $visitor = $this->visitor($site, $visitorUid, $attribution, $now);
            $session = $this->session($site, $visitor, $sessionUid, $attribution, $now, $userAgent);

            MarketingEvent::create([
                'site_id' => $site->id,
                'visitor_id' => $visitor->id,
                'session_id' => $session->id,
                'type' => $type,
                'path' => $this->clip($this->path($url), 1024),
                'title' => $this->clip($payload['title'] ?? null, 255),
                'referrer' => $this->clip($referrer, 1024),
                'payload' => $this->eventPayload($type, $payload),
                'occurred_at' => $now,
            ]);

            if ($type === MarketingEvent::TYPE_PAGEVIEW) {
                $session->increment('pageviews');
            }

            if ($type === MarketingEvent::TYPE_SIGNUP) {
                $this->identify($visitor, $session, $payload, $now);
            }

            $visitor->forceFill(['last_seen_at' => $now])->save();
            $session->forceFill(['last_event_at' => $now])->save();

            if (! $site->isVerified()) {
                $site->forceFill(['verified_at' => $now])->save();
            }

            return ['visitor_uid' => $visitor->visitor_uid, 'session_uid' => $session->session_uid];
        });
    }

    private function site(string $key): ?MarketingSite
    {
        if ($key === '') {
            return null;
        }

        return MarketingSite::where('site_key', $key)->where('is_active', true)->first();
    }

    /** Only known types, or a short custom name; never raw caller input. */
    private function type(string $type): string
    {
        $type = strtolower(trim($type));

        if ($type === '' || ! preg_match('/^[a-z0-9_-]{1,32}$/', $type)) {
            return MarketingEvent::TYPE_PAGEVIEW;
        }

        return $type;
    }

    /** A caller-supplied id is honoured only if it is a well-formed uuid. */
    private function uuid(mixed $value): string
    {
        return is_string($value) && Str::isUuid($value) ? $value : (string) Str::uuid();
    }

    /**
     * Where this visit came from, read off the landing URL and the referrer.
     *
     * UTM tags win when present, because that is what the campaign explicitly declared.
     * Otherwise a click id means paid, a search host means organic, a social host means
     * social, another host means referral, and nothing at all means direct.
     *
     * @return array<string, string|null>
     */
    public function attribution(string $url, string $referrer, string $siteDomain): array
    {
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $query = array_change_key_case($query, CASE_LOWER);

        $clickId = null;
        $clickSource = null;
        foreach (self::CLICK_IDS as $param => $platform) {
            if (! empty($query[$param]) && is_string($query[$param])) {
                $clickId = $query[$param];
                $clickSource = $platform;
                break;
            }
        }

        $source = $this->str($query['utm_source'] ?? null);
        $medium = $this->str($query['utm_medium'] ?? null);

        if ($source === null || $medium === null) {
            [$derivedSource, $derivedMedium] = $this->fromReferrer($referrer, $siteDomain, $clickSource);
            $source ??= $derivedSource;
            $medium ??= $derivedMedium;
        }

        return [
            'source' => $this->clip($source, 255),
            'medium' => $this->clip($medium, 255),
            'campaign' => $this->clip($this->str($query['utm_campaign'] ?? null), 255),
            'term' => $this->clip($this->str($query['utm_term'] ?? null), 255),
            'content' => $this->clip($this->str($query['utm_content'] ?? null), 255),
            'referrer' => $this->clip($referrer, 1024),
            'landing_path' => $this->clip($this->path($url), 1024),
            'click_id' => $this->clip($clickId, 255),
            'click_source' => $clickSource,
        ];
    }

    /** @return array{0: string, 1: string} source, medium */
    private function fromReferrer(string $referrer, string $siteDomain, ?string $clickSource): array
    {
        if ($clickSource !== null) {
            return [$clickSource, 'cpc'];
        }

        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if ($host === '' || $host === MarketingSite::normalizeDomain($siteDomain)) {
            return ['direct', 'none'];
        }

        foreach (self::SEARCH_HOSTS as $engine) {
            if (str_contains($host, $engine)) {
                return [$engine, 'organic'];
            }
        }

        foreach (self::SOCIAL_HOSTS as $network) {
            if (str_contains($host, $network)) {
                return [$network, 'social'];
            }
        }

        return [$host, 'referral'];
    }

    /**
     * @param  array<string, string|null>  $attribution
     */
    private function visitor(MarketingSite $site, string $visitorUid, array $attribution, Carbon $now): MarketingVisitor
    {
        $visitor = MarketingVisitor::where('site_id', $site->id)->where('visitor_uid', $visitorUid)->first();

        if ($visitor !== null) {
            return $visitor;
        }

        // First touch is written once, on creation, and never overwritten: it is the visit
        // that introduced this person, whatever they do later.
        return MarketingVisitor::create([
            'site_id' => $site->id,
            'visitor_uid' => $visitorUid,
            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'first_source' => $attribution['source'],
            'first_medium' => $attribution['medium'],
            'first_campaign' => $attribution['campaign'],
            'first_term' => $attribution['term'],
            'first_content' => $attribution['content'],
            'first_referrer' => $attribution['referrer'],
            'first_landing_path' => $attribution['landing_path'],
            'first_click_id' => $attribution['click_id'],
            'first_click_source' => $attribution['click_source'],
        ]);
    }

    /**
     * @param  array<string, string|null>  $attribution
     */
    private function session(MarketingSite $site, MarketingVisitor $visitor, string $sessionUid, array $attribution, Carbon $now, ?string $userAgent): MarketingSession
    {
        $session = MarketingSession::where('site_id', $site->id)->where('session_uid', $sessionUid)->first();

        // A visit that has gone quiet for the timeout is over; the next event starts a new one.
        if ($session !== null && $session->last_event_at->diffInMinutes($now) < MarketingSession::TIMEOUT_MINUTES) {
            return $session;
        }

        return MarketingSession::create([
            'site_id' => $site->id,
            'visitor_id' => $visitor->id,
            'session_uid' => $session === null ? $sessionUid : (string) Str::uuid(),
            'started_at' => $now,
            'last_event_at' => $now,
            'source' => $attribution['source'],
            'medium' => $attribution['medium'],
            'campaign' => $attribution['campaign'],
            'term' => $attribution['term'],
            'content' => $attribution['content'],
            'referrer' => $attribution['referrer'],
            'landing_path' => $attribution['landing_path'],
            'click_id' => $attribution['click_id'],
            'click_source' => $attribution['click_source'],
            'device' => $this->device($userAgent),
            'browser' => $this->browser($userAgent),
        ]);
    }

    /**
     * A signup names the person behind the browser. Every session already recorded against
     * this visitor becomes part of their journey, which is why the timeline can start
     * before the practice knew who they were.
     *
     * @param  array<string, mixed>  $payload
     */
    private function identify(MarketingVisitor $visitor, MarketingSession $session, array $payload, Carbon $now): void
    {
        $data = is_array($payload['d'] ?? null) ? $payload['d'] : [];

        $email = filter_var($this->str($data['email'] ?? null) ?? '', FILTER_VALIDATE_EMAIL) ?: null;
        $phone = $this->phone($data['phone'] ?? null);
        $name = $this->clip($this->str($data['name'] ?? null), 255);

        $visitor->forceFill(array_filter([
            'identified_at' => $visitor->identified_at ?? $now,
            'email' => $email ?? $visitor->email,
            'phone' => $phone ?? $visitor->phone,
            'name' => $name ?? $visitor->name,
        ], fn ($value) => $value !== null))->save();

        $session->forceFill(['has_signup' => true])->save();
    }

    /**
     * What the site sent with the event. Contact details are stored on the visitor, so the
     * payload keeps the rest — which form, which button — without duplicating them.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function eventPayload(string $type, array $payload): ?array
    {
        $data = is_array($payload['d'] ?? null) ? $payload['d'] : [];
        unset($data['email'], $data['phone'], $data['name']);

        // Never store free text a visitor typed: only short, declared values.
        $clean = [];
        foreach (array_slice($data, 0, 20, true) as $key => $value) {
            if (is_scalar($value) && is_string($key)) {
                $clean[$this->clip($key, 64)] = $this->clip((string) $value, 255);
            }
        }

        return $clean === [] ? null : $clean;
    }

    private function phone(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        return strlen($digits) >= 7 ? substr($digits, 0, 32) : null;
    }

    private function device(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'ipad') || str_contains($ua, 'tablet') => 'tablet',
            str_contains($ua, 'mobi') || str_contains($ua, 'android') => 'mobile',
            default => 'desktop',
        };
    }

    private function browser(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'chrome') => 'Chrome',
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'safari') => 'Safari',
            default => 'Other',
        };
    }

    private function path(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : ($url === '' ? null : '/');
    }

    private function str(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function clip(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $length);
    }
}
