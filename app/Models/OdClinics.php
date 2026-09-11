<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdClinics extends Model
{
    use BelongsToOffice;

    protected $table = 'od_clinics';

    protected $primaryKey = 'ClinicNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'ClinicNum',
        'Description',
        'Abbr',
        'Phone',
        'Fax',
        'Address',
        'Address2',
        'City',
        'State',
        'Zip',
        'IsHidden',
        'ItemOrder',
    ];

    protected function casts(): array
    {
        return [
            'ClinicNum' => 'integer',
            'office_id' => 'integer',
            'IsHidden' => 'boolean',
            'ItemOrder' => 'integer',
        ];
    }
}
