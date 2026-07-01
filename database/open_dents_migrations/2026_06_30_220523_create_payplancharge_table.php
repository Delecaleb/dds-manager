<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('payplancharge', function(Blueprint $table){

$table->integer('PayPlanChargeNum');

$table->integer('PayPlanNum');

$table->integer('Guarantor');

$table->integer('PatNum');

$table->date('ChargeDate');

$table->string('Principal');

$table->string('Interest');

$table->text('Note');

$table->integer('ProvNum');

$table->integer('ClinicNum');

$table->integer('ChargeType');

$table->integer('ProcNum');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');

$table->integer('StatementNum');

$table->integer('FKey');

$table->integer('LinkType');

$table->integer('IsOffset');

$table->integer('IsDownPayment');



});

}


public function down()
{
Schema::dropIfExists('payplancharge');
}

};
