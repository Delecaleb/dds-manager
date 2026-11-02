<?php

namespace App\Domain\Marketing;

use InvalidArgumentException;

/**
 * The Growth Engine glossary: what every marketing number is called, what it means in one
 * plain sentence, and the icon and colour that mark it on every page. Stat cards
 * (x-marketing.stat metric=…) and column headers (x-marketing.th metric=…) read from here,
 * so a metric is named and coloured the same on every page.
 *
 * Rename or redefine a metric here and nowhere else.
 *
 * How the website numbers fit together:
 *  - every visit has exactly one traffic source and one entry page, so visits per source
 *    and "visits started here" per page both add up to the total visits;
 *  - unique visitors do not add up across rows: a person who came from Google one day and
 *    typed the address the next appears under both sources, but once in the total.
 */
final class MarketingMetric
{
    /** key => [label, meaning, lucide icon, tone] */
    private const TERMS = [
        // Website — people and their visits.
        'visitors' => ['Unique visitors', 'Different people who visited. Each device counts once in the period, however often it came back.', 'users', 'sky'],
        'visits' => ['Visits', 'Each time someone came to the site. A visit ends after 30 minutes without activity.', 'log-in', 'indigo'],
        'pageviews' => ['Page views', 'Pages opened. Opening the same page twice counts twice.', 'file-text', 'violet'],
        'entrances' => ['Visits started here', 'Visits whose first page was this one: where people landed.', 'door-open', 'indigo'],
        'signups' => ['Signups', 'Visits in which the person filled in a form with their contact details.', 'user-plus', 'emerald'],
        'signup_rate' => ['Signup rate', 'Out of every 100 visits, how many ended in a signup.', 'percent', 'emerald'],
        'time_to_signup' => ['Time to sign up', 'Average time from a person\'s first visit to their signup.', 'timer', 'amber'],
        'share_visitors' => ['% of all visitors', 'Share of the period\'s unique visitors. Rows can add up to more than 100%: one person can come from several sources and see many pages.', 'users', 'sky'],
        'share_visits' => ['% of all visits', 'Share of the period\'s visits. Rows add up to 100%.', 'log-in', 'indigo'],
        'source' => ['Came from', 'Where the visit came from: the site or platform, then the kind of link. organic = unpaid search, cpc = paid ad, referral = a link on another site, none = typed in or bookmarked.', 'share-2', 'slate'],
        'last_seen' => ['Last activity', 'The most recent page view or event from this site.', 'clock', 'slate'],
        'sites' => ['Sites', 'Tracked websites assigned to the location.', 'globe', 'slate'],

        // Website — visits from ads (the landing link carried an ad click id).
        'paid_visits' => ['Visits from ads', 'Visits that arrived by clicking an ad (the link carried an ad click id).', 'megaphone', 'indigo'],
        'paid_signups' => ['Signups from ads', 'Signups among the visits from ads.', 'badge-check', 'emerald'],

        // Leads — people, not visits.
        'leads' => ['Leads', 'People who gave their contact details. One person counts once, even if they filled in several forms.', 'contact', 'emerald'],
        'leads_with_email' => ['With email', 'Leads who left an email address.', 'mail', 'slate'],
        'leads_with_phone' => ['With phone', 'Leads who left a phone number.', 'phone', 'slate'],
        'leads_from_ads' => ['First came from an ad', 'Leads whose first visit arrived by clicking an ad.', 'megaphone', 'indigo'],
        'leads_returning' => ['Came back before signing up', 'Leads who visited more than once before giving their details.', 'repeat', 'sky'],

        // Ad platforms — as Google Ads / Meta Ads report them.
        'ad_spend' => ['Ad spend', 'What the ad platforms charged in the period.', 'wallet', 'rose'],
        'impressions' => ['Ad impressions', 'Times your ads were shown.', 'eye', 'amber'],
        'ad_clicks' => ['Ad clicks', 'Clicks on your ads, as the platform counts them (Meta: link clicks).', 'mouse-pointer-click', 'amber'],
        'cpc' => ['Cost per click', 'Ad spend ÷ ad clicks.', 'receipt', 'rose'],
        'conversions' => ['Platform conversions', 'Conversions as the ad platform counts them with its own tracking. Not the same as Signups.', 'target', 'violet'],
        'cost_per_conversion' => ['Cost per conversion', 'Ad spend ÷ platform conversions.', 'calculator', 'rose'],
        'cost_per_signup' => ['Cost per signup', 'Ad spend ÷ signups on the site.', 'calculator', 'rose'],
        'campaigns' => ['Campaigns', 'Ad campaigns credited to the location.', 'flag', 'slate'],
        'active_campaigns' => ['Active campaigns', 'Campaigns switched on at the ad platform right now.', 'flag', 'emerald'],
        'ctr' => ['Click rate', 'Ad clicks ÷ ad impressions.', 'percent', 'amber'],
        'daily_budget' => ['Daily budget', 'The campaign\'s daily budget at the platform.', 'piggy-bank', 'slate'],
    ];

    /** Tone → classes for the icon chip (cards) and the small icon (headers). */
    public const TONES = [
        'slate' => ['chip' => 'bg-slate-100 text-slate-500', 'icon' => 'text-slate-400'],
        'sky' => ['chip' => 'bg-sky-50 text-sky-600', 'icon' => 'text-sky-500'],
        'indigo' => ['chip' => 'bg-indigo-50 text-indigo-600', 'icon' => 'text-indigo-500'],
        'violet' => ['chip' => 'bg-violet-50 text-violet-600', 'icon' => 'text-violet-500'],
        'emerald' => ['chip' => 'bg-emerald-50 text-emerald-600', 'icon' => 'text-emerald-500'],
        'amber' => ['chip' => 'bg-amber-50 text-amber-600', 'icon' => 'text-amber-500'],
        'rose' => ['chip' => 'bg-rose-50 text-rose-600', 'icon' => 'text-rose-500'],
    ];

    public static function label(string $key): string
    {
        return self::term($key)[0];
    }

    public static function help(string $key): string
    {
        return self::term($key)[1];
    }

    public static function icon(string $key): string
    {
        return self::term($key)[2];
    }

    public static function tone(string $key): string
    {
        return self::term($key)[3];
    }

    /** @return array{0: string, 1: string, 2: string, 3: string} */
    private static function term(string $key): array
    {
        return self::TERMS[$key] ?? throw new InvalidArgumentException("Unknown marketing metric [{$key}].");
    }
}
