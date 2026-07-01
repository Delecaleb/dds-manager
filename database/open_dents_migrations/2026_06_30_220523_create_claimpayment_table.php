<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimpayment', function(Blueprint $table){

$table->integer('ClaimPaymentNum');

$table->date('CheckDate');

$table->string('CheckAmt');

$table->string('CheckNum');

$table->string('BankBranch');

$table->text('Note');

$table->integer('ClinicNum');

$table->integer('DepositNum');

$table->string('CarrierName');

$table->date('DateIssued');

$table->integer('IsPartial');

$table->integer('PayType');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->integer('PayGroup');



});

}


public function down()
{
Schema::dropIfExists('claimpayment');
}

};
