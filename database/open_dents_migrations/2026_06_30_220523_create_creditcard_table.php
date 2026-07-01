<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('creditcard', function(Blueprint $table){

$table->integer('CreditCardNum');

$table->integer('PatNum');

$table->string('Address');

$table->string('Zip');

$table->string('XChargeToken');

$table->string('CCNumberMasked');

$table->date('CCExpiration');

$table->integer('ItemOrder');

$table->string('ChargeAmt');

$table->date('DateStart');

$table->date('DateStop');

$table->string('Note');

$table->integer('PayPlanNum');

$table->string('PayConnectToken');

$table->date('PayConnectTokenExp');

$table->text('Procedures');

$table->integer('CCSource');

$table->integer('ClinicNum');

$table->integer('ExcludeProcSync');

$table->string('PaySimpleToken');

$table->string('ChargeFrequency');

$table->integer('CanChargeWhenNoBal');

$table->integer('PaymentType');

$table->integer('IsRecurringActive');

$table->string('Nickname');

$table->string('CardHolderName');



});

}


public function down()
{
Schema::dropIfExists('creditcard');
}

};
