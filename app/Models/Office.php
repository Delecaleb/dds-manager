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
        if (app()->bound('session')) {
            $sessionOfficeId = session('active_office_id');

            if ($sessionOfficeId && static::where('id', $sessionOfficeId)->where('is_active', true)->exists()) {
                return (int) $sessionOfficeId;
            }

            if (session()->has('selected_locations')) {
                $saved = session('selected_locations');
                $keys = is_array($saved) ? $saved : explode(',', (string) $saved);
                foreach ($keys as $k) {
                    if ($k === 'all') {
                        continue;
                    }
                    $officeId = (int) explode(':', (string) $k)[0];
                    if ($officeId > 0 && static::where('id', $officeId)->where('is_active', true)->exists()) {
                        session(['active_office_id' => $officeId]);

                        return $officeId;
                    }
                }
            }
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
