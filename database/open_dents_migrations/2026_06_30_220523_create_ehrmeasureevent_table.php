<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrmeasureevent', function(Blueprint $table){

$table->integer('EhrMeasureEventNum');

$table->date('DateTEvent');

$table->integer('EventType');

$table->integer('PatNum');

$table->string('MoreInfo');

$table->string('CodeValueEvent');

$table->string('CodeSystemEvent');

$table->string('CodeValueResult');

$table->string('CodeSystemResult');

$table->integer('FKey');

$table->integer('TobaccoCessationDesire');

$table->date('DateStartTobacco');



});

}


public function down()
{
Schema::dropIfExists('ehrmeasureevent');
}

};
