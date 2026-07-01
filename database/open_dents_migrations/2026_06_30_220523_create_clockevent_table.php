<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('clockevent', function(Blueprint $table){

$table->integer('ClockEventNum');

$table->integer('EmployeeNum');

$table->date('TimeEntered1');

$table->date('TimeDisplayed1');

$table->integer('ClockStatus');

$table->text('Note');

$table->date('TimeEntered2');

$table->date('TimeDisplayed2');

$table->string('OTimeHours');

$table->string('OTimeAuto');

$table->string('Adjust');

$table->string('AdjustAuto');

$table->integer('AdjustIsOverridden');

$table->string('Rate2Hours');

$table->string('Rate2Auto');

$table->integer('ClinicNum');

$table->string('Rate3Hours');

$table->string('Rate3Auto');

$table->integer('IsWorkingHome');



});

}


public function down()
{
Schema::dropIfExists('clockevent');
}

};
