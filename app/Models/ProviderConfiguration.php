<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderConfiguration extends Model
{
    protected $table = 'provider_configurations';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'prov_num',
        'is_visible',
        'specialty',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public static function forProvider(int $officeId, int $provNum, ?int $clinicNum = null): ?self
    {
        return static::where('office_id', $officeId)
            ->where('clinic_num', $clinicNum)
            ->where('prov_num', $provNum)
            ->first();
    }
}
