<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicSetting extends Model
{
    protected $table = 'basic_settings';

    protected $fillable = [
        'office_id',
        'clinic_num',
        'display_gross_production',
        'display_net_production',
        'display_adjustment',
        'display_new_patient_tile',
        'display_new_patient_graph',
        'display_patient_visits_graph',
        'front_office_inactive_patients',
        'collection_rate_metric',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'display_gross_production' => 'boolean',
            'display_net_production' => 'boolean',
            'display_adjustment' => 'boolean',
            'display_new_patient_tile' => 'boolean',
            'display_new_patient_graph' => 'boolean',
            'display_patient_visits_graph' => 'boolean',
            'front_office_inactive_patients' => 'boolean',
        ];
    }

    /**
     * Get or create basic settings for a given office and clinic, falling back to global/defaults.
     */
    public static function forOffice(?int $officeId = null, ?int $clinicNum = null): self
    {
        $setting = null;

        if ($officeId !== null) {
            $setting = static::where('office_id', $officeId)
                ->where('clinic_num', $clinicNum)
                ->first();

            // Fallback to office-wide if specific clinic setting is not present
            if (! $setting && $clinicNum !== null) {
                $setting = static::where('office_id', $officeId)
                    ->whereNull('clinic_num')
                    ->first();
            }
        }

        // Fallback to global defaults if not found
        if (! $setting) {
            $setting = static::whereNull('office_id')->whereNull('clinic_num')->first();
        }

        if (! $setting) {
            $setting = new static([
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
                'display_gross_production' => true,
                'display_net_production' => true,
                'display_adjustment' => true,
                'display_new_patient_tile' => true,
                'display_new_patient_graph' => true,
                'display_patient_visits_graph' => true,
                'front_office_inactive_patients' => true,
                'collection_rate_metric' => 'net',
            ]);
        }

        return $setting;
    }
}
