<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('payment', function(Blueprint $table){

$table->integer('PayNum');

$table->integer('PayType');

$table->date('PayDate');

$table->string('PayAmt');

$table->string('CheckNum');

$table->string('BankBranch');

$table->text('PayNote');

$table->integer('IsSplit');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->date('DateEntry');

$table->integer('DepositNum');

$table->text('Receipt');

$table->integer('IsRecurringCC');

$table->integer('SecUserNumEntry');

$table->string('SecDateTEdit');

$table->integer('PaymentSource');

$table->integer('ProcessStatus');

$table->date('RecurringChargeDate');

$table->string('ExternalId');

$table->integer('PaymentStatus');

$table->integer('IsCcCompleted');

$table->string('MerchantFee');



});

}


public function down()
{
Schema::dropIfExists('payment');
}

};
