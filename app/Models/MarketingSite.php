<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A website reporting to the Growth Engine. Its site_key is public: it says which site an
 * event belongs to and nothing more, so it is safe to sit in page source.
 */
class MarketingSite extends Model
{
    use HasFactory;
    use HasMarketingLocation;

    protected $fillable = ['site_key', 'name', 'domain', 'office_id', 'clinic_num', 'is_active', 'verified_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public static function generateKey(): string
    {
        return 'dds_'.Str::lower(Str::random(24));
    }

    /** Hostname only, so "https://Example.com/path" and "example.com" resolve the same. */
    public static function normalizeDomain(string $domain): string
    {
        $domain = trim(strtolower($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = explode('/', $domain)[0];

        return preg_replace('/^www\./', '', $domain) ?? $domain;
    }

    /**
     * The install snippet for this site: the ONE definition, used by the Tracking Script
     * page, the WordPress instructions and sites built by the AI Website Builder.
     */
    public function trackingSnippet(?string $scriptUrl = null): string
    {
        return self::snippetFor($this->site_key, $scriptUrl ?? route('tracking.script'));
    }

    public static function snippetFor(string $siteKey, string $scriptUrl): string
    {
        return "<!-- DDS Growth Engine -->\n"
            ."<script>\n"
            ."  (function (w, d, k) {\n"
            ."    w.ddsq = w.ddsq || [];\n"
            ."    w.dds = w.dds || function () { w.ddsq.push(arguments); };\n"
            ."    var s = d.createElement('script');\n"
            ."    s.async = 1;\n"
            ."    s.src = '{$scriptUrl}';\n"
            ."    s.dataset.key = k;\n"
            ."    d.head.appendChild(s);\n"
            ."  })(window, document, '{$siteKey}');\n"
            .'</script>';
    }

    /** The call a site makes when a contact form is submitted (the example shown on the page). */
    public const SIGNUP_EXAMPLE = "dds('signup', { email: 'patient@example.com', phone: '(313) 555-0142', name: 'Jane Doe', form: 'book-consult' });";

    /**
     * Plain-text install instructions for whoever maintains the website, with this site's real
     * snippet in it — what the "Copy instructions for your developer" button copies.
     */
    public function installInstructions(?string $scriptUrl = null): string
    {
        return "Website tracking for {$this->domain} ({$this->name})\n\n"
            ."1. Paste this snippet just before </head> on every page of {$this->domain}:\n\n"
            .$this->trackingSnippet($scriptUrl)."\n\n"
            ."2. When a contact or booking form is submitted successfully, call:\n\n"
            .self::SIGNUP_EXAMPLE."\n\n"
            ."   Pass the form's real email, phone and name values, and a short form name.\n\n"
            ."Notes:\n"
            ."- WordPress: use a header plugin such as WPCode (Header & Footer -> Header), not a single page.\n"
            ."- Clear any page cache (WP Rocket, LiteSpeed, Cloudflare) after installing.\n"
            ."- It collects page views, traffic sources and what the form passes at signup; never keystrokes, health details or IP addresses.\n"
            ."- Once installed, the site shows as Verified in the Growth Engine within a minute of the first visit.\n";
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(MarketingVisitor::class, 'site_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MarketingSession::class, 'site_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'site_id');
    }
}
