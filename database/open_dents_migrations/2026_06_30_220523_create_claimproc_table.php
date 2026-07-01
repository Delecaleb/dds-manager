<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimproc', function(Blueprint $table){

$table->integer('ClaimProcNum');

$table->integer('ProcNum');

$table->integer('ClaimNum');

$table->integer('PatNum');

$table->integer('ProvNum');

$table->string('FeeBilled');

$table->string('InsPayEst');

$table->string('DedApplied');

$table->integer('Status');

$table->string('InsPayAmt');

$table->string('Remarks');

$table->integer('ClaimPaymentNum');

$table->integer('PlanNum');

$table->date('DateCP');

$table->string('WriteOff');

$table->string('CodeSent');

$table->string('AllowedOverride');

$table->integer('Percentage');

$table->integer('PercentOverride');

$table->string('CopayAmt');

$table->integer('NoBillIns');

$table->string('PaidOtherIns');

$table->string('BaseEst');

$table->string('CopayOverride');

$table->date('ProcDate');

$table->date('DateEntry');

$table->integer('LineNumber');

$table->string('DedEst');

$table->string('DedEstOverride');

$table->string('InsEstTotal');

$table->string('InsEstTotalOverride');

$table->string('PaidOtherInsOverride');

$table->string('EstimateNote');

$table->string('WriteOffEst');

$table->string('WriteOffEstOverride');

$table->integer('ClinicNum');

$table->integer('InsSubNum');

$table->integer('PaymentRow');

$table->integer('PayPlanNum');

$table->integer('ClaimPaymentTracking');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->date('DateSuppReceived');

$table->date('DateInsFinalized');

$table->integer('IsTransfer');

$table->string('ClaimAdjReasonCodes');

$table->integer('IsOverpay');

$table->string('SecurityHash');

$table->integer('Etrans835AttachNum');



});

}


public function down()
{
Schema::dropIfExists('claimproc');
}

};
