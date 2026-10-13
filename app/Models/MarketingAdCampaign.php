<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One campaign in an ad account. office_id/clinic_num is the location it is credited to,
 * always one of its account's locations. Unset: it counts for the account's location when
 * the account has exactly one, and for no location otherwise.
 */
class MarketingAdCampaign extends Model
{
    use HasMarketingLocation;

    protected $fillable = [
        'account_id', 'external_id', 'name', 'status', 'channel_type', 'daily_budget_micros', 'office_id', 'clinic_num',
    ];

    protected function casts(): array
    {
        return [
            'daily_budget_micros' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketingAdAccount::class, 'account_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(MarketingAdCampaignStat::class, 'campaign_id');
    }
}
