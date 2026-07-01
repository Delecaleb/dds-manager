<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('scheduleop', function(Blueprint $table){

$table->integer('ScheduleOpNum');

$table->integer('ScheduleNum');

$table->integer('OperatoryNum');



});

}


public function down()
{
Schema::dropIfExists('scheduleop');
}

};
