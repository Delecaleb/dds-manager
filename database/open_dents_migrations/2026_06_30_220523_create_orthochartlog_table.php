<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('orthochartlog', function(Blueprint $table){

$table->integer('OrthoChartLogNum');

$table->integer('PatNum');

$table->string('ComputerName');

$table->date('DateTimeLog');

$table->date('DateTimeService');

$table->integer('UserNum');

$table->integer('ProvNum');

$table->integer('OrthoChartRowNum');

$table->text('LogData');



});

}


public function down()
{
Schema::dropIfExists('orthochartlog');
}

};
