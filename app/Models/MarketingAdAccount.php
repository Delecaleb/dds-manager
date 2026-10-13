<?php

namespace App\Models;

use App\Domain\Support\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One ad account (a Google Ads "customer"), stored once whichever login can see it.
 *
 * It is reported for the locations an admin assigns it to, and synced only once it has at
 * least one. With one location every campaign counts for it; with several, each campaign
 * must be assigned to one of them (MarketingAdCampaign) and counts for no location until
 * it is. connection_id is the login it is currently read through.
 */
class MarketingAdAccount extends Model
{
    protected $fillable = [
        'connection_id', 'provider', 'external_id', 'login_customer_id', 'name', 'currency_code', 'time_zone',
        'is_manager', 'status', 'history_from', 'last_synced_at', 'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_manager' => 'boolean',
            'history_from' => 'date',
            'last_synced_at' => 'datetime',
        ];
    }

    /** Accounts that can hold campaigns: not a manager, and not closed or suspended at Google. */
    public function scopeAdvertising(Builder $query): Builder
    {
        return $query->where('is_manager', false)
            ->where(fn (Builder $q) => $q->whereNull('status')->orWhere('status', 'ENABLED'));
    }

    /** Assigned to at least one location, so it may be synced and reported. */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereHas('locations');
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MarketingAdConnection::class, 'connection_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(MarketingAdAccountLocation::class, 'account_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingAdCampaign::class, 'account_id');
    }

    /** @return list<string> the ClinicRegistry keys of the locations this account is assigned to */
    public function locationKeys(): array
    {
        return $this->locations->map(fn (MarketingAdAccountLocation $l) => $l->locationKey())->values()->all();
    }

    /**
     * Replace the account's locations. Campaigns assigned to a location the account no
     * longer has become unassigned, so nothing keeps counting for a location it left.
     *
     * @param  list<Location>  $locations
     */
    public function assignLocations(array $locations): void
    {
        $this->locations()->delete();

        foreach ($locations as $location) {
            $this->locations()->create(['office_id' => $location->officeId, 'clinic_num' => $location->clinicNum]);
        }

        $keep = array_map(fn (Location $l) => $l->key(), $locations);
        $this->campaigns()->whereNotNull('office_id')->get()
            ->reject(fn (MarketingAdCampaign $c) => in_array($c->locationKey(), $keep, true))
            ->each(fn (MarketingAdCampaign $c) => $c->assignLocation(null)->save());

        $this->unsetRelation('locations');
    }
}
