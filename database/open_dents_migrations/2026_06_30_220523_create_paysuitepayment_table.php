<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('paysuitepayment', function(Blueprint $table){

$table->integer('PaySuitePaymentNum');

$table->string('PaymentId');

$table->string('ProviderId');

$table->string('PaymentMethod');

$table->string('PaymentReference');

$table->string('PaymentAmount');

$table->date('PaymentDate');

$table->string('PaymentStatus');

$table->string('ReversalReasonCode');

$table->string('AssociatedPaymentId');

$table->integer('PaySuitePaymentDetailNum');

$table->integer('HasUnresolvedClaimPayment');

$table->integer('ReconciliationStatus');

$table->integer('ClaimPaymentNum');



});

}


public function down()
{
Schema::dropIfExists('paysuitepayment');
}

};
