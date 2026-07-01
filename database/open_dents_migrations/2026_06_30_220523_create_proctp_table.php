<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('proctp', function(Blueprint $table){

$table->integer('ProcTPNum');

$table->integer('TreatPlanNum');

$table->integer('PatNum');

$table->integer('ProcNumOrig');

$table->integer('ItemOrder');

$table->integer('Priority');

$table->string('ToothNumTP');

$table->string('Surf');

$table->string('ProcCode');

$table->string('Descript');

$table->string('FeeAmt');

$table->string('PriInsAmt');

$table->string('SecInsAmt');

$table->string('PatAmt');

$table->string('Discount');

$table->string('Prognosis');

$table->string('Dx');

$table->string('ProcAbbr');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->string('FeeAllowed');

$table->string('TaxAmt');

$table->integer('ProvNum');

$table->date('DateTP');

$table->integer('ClinicNum');

$table->string('CatPercUCR');



});

}


public function down()
{
Schema::dropIfExists('proctp');
}

};
