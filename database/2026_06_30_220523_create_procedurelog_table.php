<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('procedurelog', function(Blueprint $table){

$table->integer('ProcNum');

$table->integer('PatNum');

$table->integer('AptNum');

$table->string('OldCode');

$table->date('ProcDate');

$table->string('ProcFee');

$table->string('Surf');

$table->string('ToothNum');

$table->string('ToothRange');

$table->integer('Priority');

$table->integer('ProcStatus');

$table->integer('ProvNum');

$table->integer('Dx');

$table->integer('PlannedAptNum');

$table->integer('PlaceService');

$table->string('Prosthesis');

$table->date('DateOriginalProsth');

$table->string('ClaimNote');

$table->date('DateEntryC');

$table->integer('ClinicNum');

$table->string('MedicalCode');

$table->string('DiagnosticCode');

$table->integer('IsPrincDiag');

$table->integer('ProcNumLab');

$table->integer('BillingTypeOne');

$table->integer('BillingTypeTwo');

$table->integer('CodeNum');

$table->string('CodeMod1');

$table->string('CodeMod2');

$table->string('CodeMod3');

$table->string('CodeMod4');

$table->string('RevCode');

$table->integer('UnitQty');

$table->integer('BaseUnits');

$table->integer('StartTime');

$table->integer('StopTime');

$table->date('DateTP');

$table->integer('SiteNum');

$table->integer('HideGraphics');

$table->string('CanadianTypeCodes');

$table->string('ProcTime');

$table->string('ProcTimeEnd');

$table->string('DateTStamp');

$table->integer('Prognosis');

$table->integer('DrugUnit');

$table->string('DrugQty');

$table->integer('UnitQtyType');

$table->integer('StatementNum');

$table->integer('IsLocked');

$table->string('BillingNote');

$table->integer('RepeatChargeNum');

$table->string('SnomedBodySite');

$table->string('DiagnosticCode2');

$table->string('DiagnosticCode3');

$table->string('DiagnosticCode4');

$table->integer('ProvOrderOverride');

$table->string('Discount');

$table->integer('IsDateProsthEst');

$table->integer('IcdVersion');

$table->integer('IsCpoe');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->date('DateComplete');

$table->integer('OrderingReferralNum');

$table->string('TaxAmt');

$table->integer('Urgency');

$table->string('DiscountPlanAmt');

$table->integer('NoBillIns');



});

}


public function down()
{
Schema::dropIfExists('procedurelog');
}

};
