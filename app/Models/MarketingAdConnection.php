<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One authorised ad platform login: a Google login, or a Meta Business Manager system user.
 * It belongs to no location: it is only the means of reaching ad accounts. Which location
 * an account's spend counts for is set per account (and per campaign), never by which login
 * found it.
 *
 * account_email names the login: the Google email, or "<system user name> (<id>)" for Meta.
 * refresh_token holds the long-lived credential: Google's OAuth refresh token, or the Meta
 * system user token itself. Either is encrypted at rest and never serialised.
 */
class MarketingAdConnection extends Model
{
    public const PROVIDER_GOOGLE_ADS = 'google_ads';

    public const PROVIDER_META_ADS = 'meta';

    /** Provider → the platform's name on screen. */
    public const PLATFORMS = [
        self::PROVIDER_GOOGLE_ADS => 'Google Ads',
        self::PROVIDER_META_ADS => 'Meta Ads',
    ];

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_ERROR = 'error';

    public const STATUS_DISCONNECTED = 'disconnected';

    protected $fillable = [
        'provider', 'account_email', 'status', 'refresh_token', 'connected_by', 'connected_at', 'last_synced_at', 'last_error',
    ];

    protected $hidden = ['refresh_token'];

    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    /** Google Ads connections only. */
    public function scopeGoogleAds(Builder $query): Builder
    {
        return $query->where('provider', self::PROVIDER_GOOGLE_ADS);
    }

    /** Meta Ads connections only. */
    public function scopeMetaAds(Builder $query): Builder
    {
        return $query->where('provider', self::PROVIDER_META_ADS);
    }

    /** True when at least one Google Ads login is connected and usable. */
    public static function googleAdsConnected(): bool
    {
        return static::query()->googleAds()->get()->contains(fn (self $c) => $c->isUsable());
    }

    /**
     * Providers with at least one usable login, e.g. ['google_ads', 'meta'].
     *
     * @return list<string>
     */
    public static function connectedProviders(): array
    {
        return static::query()->get()
            ->filter(fn (self $c) => $c->isUsable())
            ->pluck('provider')->unique()->values()->all();
    }

    /** "Google Ads", "Meta Ads", or "Google Ads + Meta Ads" for a list of providers. */
    public static function platformNames(array $providers): string
    {
        return implode(' + ', array_map(fn (string $p) => self::PLATFORMS[$p] ?? $p, $providers));
    }

    /** The platform this login reaches, on screen. */
    public function platform(): string
    {
        return self::PLATFORMS[$this->provider] ?? (string) $this->provider;
    }

    /** Usable for API calls: a readable token is held and the platform has not rejected it. */
    public function isUsable(): bool
    {
        return $this->status !== self::STATUS_DISCONNECTED && filled($this->refreshToken());
    }

    /**
     * The stored refresh token, or null when there is none — or when it cannot be decrypted
     * because it was encrypted under a different APP_KEY (a rotated key, or rows copied from
     * another server). Such a connection simply needs reconnecting; it must not break pages.
     */
    public function refreshToken(): ?string
    {
        try {
            $token = $this->refresh_token;
        } catch (DecryptException) {
            return null;
        }

        return filled($token) ? (string) $token : null;
    }

    /** True when a token is stored but this server's APP_KEY cannot read it. */
    public function hasUnreadableToken(): bool
    {
        return filled($this->getRawOriginal('refresh_token')) && $this->refreshToken() === null;
    }

    /** How the connection is named on screen: the login's identity, else its number. */
    public function label(): string
    {
        return $this->account_email ?: "{$this->platform()} login #{$this->id}";
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(MarketingAdAccount::class, 'connection_id');
    }
}
