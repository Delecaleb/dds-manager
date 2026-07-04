<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdClaimPayment extends Model
{
    public $fillable = [
         'ClaimPaymentNum', 
         'CheckDate', 
         'CheckAmt',
         'CheckNum',
         'BankBranch', 
         'Note', 
         'ClinicNum', 
         'DepositNum', 
         'CarrierName', 
         'DateIssued', 
         'IsPartial', 
         'PayType', 
         'SecUserNumEntry', 
         'SecDateEntry', 
         'SecDateTEdit', 
         'PayGroup',
    ];
}
