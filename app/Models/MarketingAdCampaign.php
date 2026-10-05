<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One campaign in an ad account. office_id, when set, overrides the account's location. */
class MarketingAdCampaign extends Model
{
    protected $fillable = [
        'account_id', 'external_id', 'name', 'status', 'channel_type', 'daily_budget_micros', 'office_id',
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
