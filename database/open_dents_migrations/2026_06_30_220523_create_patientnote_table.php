<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patientnote', function(Blueprint $table){

$table->integer('PatNum');

$table->text('FamFinancial');

$table->text('ApptPhone');

$table->text('Medical');

$table->text('Service');

$table->text('MedicalComp');

$table->text('Treatment');

$table->string('ICEName');

$table->string('ICEPhone');

$table->integer('OrthoMonthsTreatOverride');

$table->date('DateOrthoPlacementOverride');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');

$table->integer('Consent');

$table->integer('UserNumOrthoLocked');

$table->integer('Pronoun');



});

}


public function down()
{
Schema::dropIfExists('patientnote');
}

};
