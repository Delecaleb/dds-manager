<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One app-wide setting (JSON value by key). Read through a typed service such as
 * SyncAlertSettings rather than by raw key, so each key has one owner.
 */
class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function valueFor(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->first()?->value ?? $default;
    }

    public static function put(string $key, mixed $value, ?int $userId = null): self
    {
        return static::query()->updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $userId]);
    }
}
