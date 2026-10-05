<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The app's authorised link to one ad platform. Holds the long-lived refresh token, which
 * is encrypted at rest and never serialised.
 */
class MarketingAdConnection extends Model
{
    public const PROVIDER_GOOGLE_ADS = 'google_ads';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_ERROR = 'error';

    public const STATUS_DISCONNECTED = 'disconnected';

    protected $fillable = [
        'provider', 'status', 'refresh_token', 'connected_by', 'connected_at', 'last_synced_at', 'last_error',
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

    public static function googleAds(): ?self
    {
        return static::where('provider', self::PROVIDER_GOOGLE_ADS)->first();
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
