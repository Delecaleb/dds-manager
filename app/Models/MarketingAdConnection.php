<?php

namespace App\Models;

use App\Domain\Marketing\LocationScope;
use App\Domain\Marketing\MarketingFilter;
use App\Domain\Support\Location;
use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One location's authorised link to one ad platform. Every office (or clinic) connects
 * its own ad account; the connection holds that location and the long-lived refresh
 * token, which is encrypted at rest and never serialised.
 */
class MarketingAdConnection extends Model
{
    use HasMarketingLocation;

    public const PROVIDER_GOOGLE_ADS = 'google_ads';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_ERROR = 'error';

    public const STATUS_DISCONNECTED = 'disconnected';

    protected $fillable = [
        'provider', 'office_id', 'clinic_num', 'status', 'refresh_token', 'connected_by', 'connected_at', 'last_synced_at', 'last_error',
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

    /** Connections credited to the filter's locations (see LocationScope for the rules). */
    public function scopeWithin(Builder $query, MarketingFilter $filter): Builder
    {
        LocationScope::apply($query, $filter, 'office_id', 'clinic_num');

        return $query;
    }

    /** The provider's connection for exactly this location (null location = not assigned). */
    public static function forLocation(string $provider, ?Location $location): ?self
    {
        return static::query()
            ->where('provider', $provider)
            ->where('office_id', $location?->officeId)
            ->where('clinic_num', $location?->clinicNum)
            ->first();
    }

    /** True when at least one usable Google Ads connection serves the filter's locations. */
    public static function googleAdsUsableWithin(MarketingFilter $filter): bool
    {
        return static::query()->googleAds()->within($filter)->get()->contains(fn (self $c) => $c->isUsable());
    }

    /** Usable for API calls: a token is held and the platform has not rejected it. */
    public function isUsable(): bool
    {
        return $this->status !== self::STATUS_DISCONNECTED && filled($this->refresh_token);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(MarketingAdAccount::class, 'connection_id');
    }
}
