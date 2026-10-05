<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** What one campaign did on one day, as reported by the ad platform. */
class MarketingAdCampaignStat extends Model
{
    protected $fillable = [
        'campaign_id', 'date', 'impressions', 'clicks', 'cost_micros', 'conversions', 'conversions_value',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'cost_micros' => 'integer',
            'conversions' => 'float',
            'conversions_value' => 'float',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingAdCampaign::class, 'campaign_id');
    }
}
