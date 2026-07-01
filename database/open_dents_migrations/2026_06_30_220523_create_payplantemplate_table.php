<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('payplantemplate', function(Blueprint $table){

$table->integer('PayPlanTemplateNum');

$table->string('PayPlanTemplateName');

$table->integer('ClinicNum');

$table->string('APR');

$table->integer('InterestDelay');

$table->string('PayAmt');

$table->integer('NumberOfPayments');

$table->integer('ChargeFrequency');

$table->string('DownPayment');

$table->integer('DynamicPayPlanTPOption');

$table->string('Note');

$table->integer('IsHidden');

$table->integer('SheetDefNum');



});

}


public function down()
{
Schema::dropIfExists('payplantemplate');
}

};
