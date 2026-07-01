<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('payplan', function(Blueprint $table){

$table->integer('PayPlanNum');

$table->integer('PatNum');

$table->integer('Guarantor');

$table->date('PayPlanDate');

$table->string('APR');

$table->text('Note');

$table->integer('PlanNum');

$table->string('CompletedAmt');

$table->integer('InsSubNum');

$table->integer('PaySchedule');

$table->integer('NumberOfPayments');

$table->string('PayAmt');

$table->string('DownPayment');

$table->integer('IsClosed');

$table->text('Signature');

$table->integer('SigIsTopaz');

$table->integer('PlanCategory');

$table->integer('IsDynamic');

$table->integer('ChargeFrequency');

$table->date('DatePayPlanStart');

$table->integer('IsLocked');

$table->date('DateInterestStart');

$table->integer('DynamicPayPlanTPOption');

$table->integer('MobileAppDeviceNum');

$table->string('SecurityHash');

$table->integer('SheetDefNum');



});

}


public function down()
{
Schema::dropIfExists('payplan');
}

};
