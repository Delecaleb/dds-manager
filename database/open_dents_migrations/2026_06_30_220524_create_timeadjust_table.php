<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('timeadjust', function(Blueprint $table){

$table->integer('TimeAdjustNum');

$table->integer('EmployeeNum');

$table->date('TimeEntry');

$table->string('RegHours');

$table->string('OTimeHours');

$table->text('Note');

$table->integer('IsAuto');

$table->integer('ClinicNum');

$table->integer('PtoDefNum');

$table->string('PtoHours');

$table->integer('IsUnpaidProtectedLeave');

$table->integer('SecuUserNumEntry');



});

}


public function down()
{
Schema::dropIfExists('timeadjust');
}

};
