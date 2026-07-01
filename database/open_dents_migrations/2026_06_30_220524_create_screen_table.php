<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('screen', function(Blueprint $table){

$table->integer('ScreenNum');

$table->integer('Gender');

$table->integer('RaceOld');

$table->integer('GradeLevel');

$table->integer('Age');

$table->integer('Urgency');

$table->integer('HasCaries');

$table->integer('NeedsSealants');

$table->integer('CariesExperience');

$table->integer('EarlyChildCaries');

$table->integer('ExistingSealants');

$table->integer('MissingAllTeeth');

$table->date('Birthdate');

$table->integer('ScreenGroupNum');

$table->integer('ScreenGroupOrder');

$table->string('Comments');

$table->integer('ScreenPatNum');

$table->integer('SheetNum');



});

}


public function down()
{
Schema::dropIfExists('screen');
}

};
