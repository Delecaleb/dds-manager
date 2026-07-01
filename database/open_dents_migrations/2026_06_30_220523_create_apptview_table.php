<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('apptview', function(Blueprint $table){

$table->integer('ApptViewNum');

$table->string('Description');

$table->integer('ItemOrder');

$table->integer('RowsPerIncr');

$table->integer('OnlyScheduledProvs');

$table->string('OnlySchedBeforeTime');

$table->string('OnlySchedAfterTime');

$table->integer('StackBehavUR');

$table->integer('StackBehavLR');

$table->integer('ClinicNum');

$table->string('ApptTimeScrollStart');

$table->integer('IsScrollStartDynamic');

$table->integer('IsApptBubblesDisabled');

$table->integer('WidthOpMinimum');

$table->integer('WaitingRmName');

$table->integer('OnlyScheduledProvDays');

$table->integer('ShowMirroredAppts');



});

}


public function down()
{
Schema::dropIfExists('apptview');
}

};
