<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('automation', function(Blueprint $table){

$table->integer('AutomationNum');

$table->text('Description');

$table->integer('Autotrigger');

$table->text('ProcCodes');

$table->integer('AutoAction');

$table->integer('SheetDefNum');

$table->integer('CommType');

$table->text('MessageContent');

$table->integer('AptStatus');

$table->integer('AppointmentTypeNum');

$table->integer('PatStatus');



});

}


public function down()
{
Schema::dropIfExists('automation');
}

};
