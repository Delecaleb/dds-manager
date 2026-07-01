<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('benefit', function(Blueprint $table){

$table->integer('BenefitNum');

$table->integer('PlanNum');

$table->integer('PatPlanNum');

$table->integer('CovCatNum');

$table->integer('BenefitType');

$table->integer('Percent');

$table->string('MonetaryAmt');

$table->integer('TimePeriod');

$table->integer('QuantityQualifier');

$table->integer('Quantity');

$table->integer('CodeNum');

$table->integer('CoverageLevel');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');

$table->integer('CodeGroupNum');

$table->integer('TreatArea');

$table->string('ToothRange');



});

}


public function down()
{
Schema::dropIfExists('benefit');
}

};
