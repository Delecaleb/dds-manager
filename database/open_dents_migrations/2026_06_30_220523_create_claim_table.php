<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claim', function(Blueprint $table){

$table->integer('ClaimNum');

$table->integer('PatNum');

$table->date('DateService');

$table->date('DateSent');

$table->string('ClaimStatus');

$table->date('DateReceived');

$table->integer('PlanNum');

$table->integer('ProvTreat');

$table->string('ClaimFee');

$table->string('InsPayEst');

$table->string('InsPayAmt');

$table->string('DedApplied');

$table->string('PreAuthString');

$table->string('IsProsthesis');

$table->date('PriorDate');

$table->string('ReasonUnderPaid');

$table->string('ClaimNote');

$table->string('ClaimType');

$table->integer('ProvBill');

$table->integer('ReferringProv');

$table->string('RefNumString');

$table->integer('PlaceService');

$table->string('AccidentRelated');

$table->date('AccidentDate');

$table->string('AccidentST');

$table->integer('EmployRelated');

$table->integer('IsOrtho');

$table->integer('OrthoRemainM');

$table->date('OrthoDate');

$table->integer('PatRelat');

$table->integer('PlanNum2');

$table->integer('PatRelat2');

$table->string('WriteOff');

$table->integer('Radiographs');

$table->integer('ClinicNum');

$table->integer('ClaimForm');

$table->integer('AttachedImages');

$table->integer('AttachedModels');

$table->string('AttachedFlags');

$table->string('AttachmentID');

$table->string('CanadianMaterialsForwarded');

$table->string('CanadianReferralProviderNum');

$table->integer('CanadianReferralReason');

$table->string('CanadianIsInitialLower');

$table->date('CanadianDateInitialLower');

$table->integer('CanadianMandProsthMaterial');

$table->string('CanadianIsInitialUpper');

$table->date('CanadianDateInitialUpper');

$table->integer('CanadianMaxProsthMaterial');

$table->integer('InsSubNum');

$table->integer('InsSubNum2');

$table->string('CanadaTransRefNum');

$table->date('CanadaEstTreatStartDate');

$table->string('CanadaInitialPayment');

$table->integer('CanadaPaymentMode');

$table->integer('CanadaTreatDuration');

$table->integer('CanadaNumAnticipatedPayments');

$table->string('CanadaAnticipatedPayAmount');

$table->string('PriorAuthorizationNumber');

$table->integer('SpecialProgramCode');

$table->string('UniformBillType');

$table->integer('MedType');

$table->string('AdmissionTypeCode');

$table->string('AdmissionSourceCode');

$table->string('PatientStatusCode');

$table->integer('CustomTracking');

$table->date('DateResent');

$table->integer('CorrectionType');

$table->string('ClaimIdentifier');

$table->string('OrigRefNum');

$table->integer('ProvOrderOverride');

$table->integer('OrthoTotalM');

$table->string('ShareOfCost');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->integer('OrderingReferralNum');

$table->date('DateSentOrig');

$table->date('DateIllnessInjuryPreg');

$table->integer('DateIllnessInjuryPregQualifier');

$table->date('DateOther');

$table->integer('DateOtherQualifier');

$table->integer('IsOutsideLab');

$table->string('SecurityHash');

$table->text('Narrative');



});

}


public function down()
{
Schema::dropIfExists('claim');
}

};
