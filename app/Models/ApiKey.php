<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A read-only Developer API key for exactly one location. The location is part of the key,
 * never of the request, so a key can only ever read its own location's data.
 *
 * Requests are matched on a SHA-256 of the key (token_hash). The key itself is also kept,
 * encrypted with APP_KEY, so its owner can copy it again after re-entering their password.
 * created_by is the developer who owns the key.
 */
class ApiKey extends Model
{
    use HasMarketingLocation;

    /** Read appointments (time, provider, status). Every key has it. */
    public const ABILITY_BOOKINGS = 'bookings:read';

    /** Also read the patient's name, email, phone numbers and address on each booking. */
    public const ABILITY_PATIENT_CONTACT = 'patients:contact';

    public const ABILITIES = [
        self::ABILITY_BOOKINGS => 'Bookings — appointment time, length, provider, operatory and status',
        self::ABILITY_PATIENT_CONTACT => 'Patient contact details — full name, email, phone numbers and address',
    ];

    protected $fillable = [
        'name', 'office_id', 'clinic_num', 'abilities', 'rate_limit_per_minute', 'created_by', 'expires_at',
    ];

    protected $hidden = ['token_hash', 'token_encrypted'];

    protected function casts(): array
    {
        return [
            'token_encrypted' => 'encrypted',
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Save a new key and return it with its plain-text value.
     *
     * @return array{0: self, 1: string}
     */
    public static function issue(self $key): array
    {
        return [$key, $key->assignNewToken()];
    }

    /**
     * Replace the key's value: the old one stops working at once. Name, location,
     * permissions and request history are kept.
     *
     * @return string the new plain-text key
     */
    public function regenerate(): string
    {
        return $this->assignNewToken();
    }

    /** The plain-text key, for showing again; null for keys issued before copies were kept. */
    public function plainKey(): ?string
    {
        return $this->token_encrypted;
    }

    private function assignNewToken(): string
    {
        $plain = config('developer_api.key_prefix').Str::random(48);

        $this->token_hash = self::hash($plain);
        $this->token_hint = substr($plain, -4);
        $this->token_encrypted = $plain;
        $this->save();

        return $plain;
    }

    /** The active key matching a presented plain-text value, if any. */
    public static function findActive(string $plain): ?self
    {
        if ($plain === '') {
            return null;
        }

        return static::query()->active()->where('token_hash', self::hash($plain))->first();
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /** Not revoked and not expired. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function allows(string $ability): bool
    {
        return in_array($ability, (array) $this->abilities, true);
    }

    /** What the admin sees in place of the key: "dds_…a1b2". */
    public function maskedKey(): string
    {
        return config('developer_api.key_prefix').'…'.$this->token_hint;
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }
}
