<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One location an ad account is assigned to (an office, or one clinic of it). */
class MarketingAdAccountLocation extends Model
{
    use HasMarketingLocation;

    protected $fillable = ['account_id', 'office_id', 'clinic_num'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketingAdAccount::class, 'account_id');
    }
}
