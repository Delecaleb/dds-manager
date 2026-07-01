<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('scheduledprocess', function(Blueprint $table){

$table->integer('ScheduledProcessNum');

$table->string('ScheduledAction');

$table->date('TimeToRun');

$table->string('FrequencyToRun');

$table->date('LastRanDateTime');



});

}


public function down()
{
Schema::dropIfExists('scheduledprocess');
}

};
