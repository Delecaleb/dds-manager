<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One ad account (a Google Ads "customer"). office_id is the location its campaigns are
 * credited to unless a campaign names its own.
 */
class MarketingAdAccount extends Model
{
    use HasMarketingLocation;

    protected $fillable = [
        'connection_id', 'external_id', 'login_customer_id', 'name', 'currency_code', 'time_zone',
        'is_manager', 'status', 'office_id', 'clinic_num', 'is_enabled', 'last_synced_at', 'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_manager' => 'boolean',
            'is_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MarketingAdConnection::class, 'connection_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingAdCampaign::class, 'account_id');
    }
}
