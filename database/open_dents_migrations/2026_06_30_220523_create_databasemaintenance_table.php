<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('databasemaintenance', function(Blueprint $table){

$table->integer('DatabaseMaintenanceNum');

$table->string('MethodName');

$table->integer('IsHidden');

$table->integer('IsOld');

$table->date('DateLastRun');



});

}


public function down()
{
Schema::dropIfExists('databasemaintenance');
}

};
