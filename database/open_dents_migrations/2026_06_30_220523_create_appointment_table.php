<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('appointment', function(Blueprint $table){

$table->integer('AptNum');

$table->integer('PatNum');

$table->integer('AptStatus');

$table->string('Pattern');

$table->integer('Confirmed');

$table->integer('TimeLocked');

$table->integer('Op');

$table->text('Note');

$table->integer('ProvNum');

$table->integer('ProvHyg');

$table->date('AptDateTime');

$table->integer('NextAptNum');

$table->integer('UnschedStatus');

$table->integer('IsNewPatient');

$table->text('ProcDescript');

$table->integer('Assistant');

$table->integer('ClinicNum');

$table->integer('IsHygiene');

$table->string('DateTStamp');

$table->date('DateTimeArrived');

$table->date('DateTimeSeated');

$table->date('DateTimeDismissed');

$table->integer('InsPlan1');

$table->integer('InsPlan2');

$table->date('DateTimeAskedToArrive');

$table->text('ProcsColored');

$table->integer('ColorOverride');

$table->integer('AppointmentTypeNum');

$table->integer('SecUserNumEntry');

$table->date('SecDateTEntry');

$table->integer('Priority');

$table->string('ProvBarText');

$table->string('PatternSecondary');

$table->string('SecurityHash');

$table->integer('ItemOrderPlanned');

$table->integer('IsMirrored');



});

}


public function down()
{
Schema::dropIfExists('appointment');
}

};
