<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdProcedure extends Model
{
    protected $fillable = [
        'CodeNum',
        'ProcCode',
        'Descript',
        'AbbrDesc',
        'ProcTime',
        'ProcCat',
        'TreatArea',
        'NoBillIns',
        'IsProsth',
        'DefaultNote',
        'IsHygiene',
        'GTypeNum',
        'AlternateCode1',
        'MedicalCode',
        'IsTaxed',
        'PaintType',
        'GraphicColor',
        'LaymanTerm',
        'IsCanadianLab',
        'PreExisting',
        'BaseUnits',
        'SubstitutionCode',
        'SubstOnlyIf',
        'DateTStamp',
        'IsMultiVisit',
        'DrugNDC',
        'RevenueCodeDefault',
        'ProvNumDefault',
        'CanadaTimeUnits',
        'IsRadiology',
        'DefaultClaimNote',
        'DefaultTPNote',
        'BypassGlobalLock',
        'TaxCode',
        'PaintText',
        'AreaAlsoToothRange',
        'DiagnosticCodes',
    ];

    protected $primaryKey = 'ProcNum';

    public $incrementing = false;
}
