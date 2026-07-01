<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('tasklist', function(Blueprint $table){

$table->integer('TaskListNum');

$table->string('Descript');

$table->integer('Parent');

$table->date('DateTL');

$table->integer('IsRepeating');

$table->integer('DateType');

$table->integer('FromNum');

$table->integer('ObjectType');

$table->date('DateTimeEntry');

$table->integer('GlobalTaskFilterType');

$table->integer('TaskListStatus');



});

}


public function down()
{
Schema::dropIfExists('tasklist');
}

};
