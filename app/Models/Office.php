<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name',
        'developer_key',
        'customer_key',
        'api_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the active office ID from session or default office.
     */
    public static function getActiveOfficeId(): ?int
    {
        $sessionOfficeId = session('active_office_id');

        if ($sessionOfficeId && static::where('id', $sessionOfficeId)->where('is_active', true)->exists()) {
            return (int) $sessionOfficeId;
        }

        $defaultOffice = static::where('is_active', true)->first();

        return $defaultOffice ? $defaultOffice->id : null;
    }

    /**
     * Get the active office model instance.
     */
    public static function getActiveOffice(): ?static
    {
        $activeId = static::getActiveOfficeId();

        return $activeId ? static::find($activeId) : null;
    }
}
