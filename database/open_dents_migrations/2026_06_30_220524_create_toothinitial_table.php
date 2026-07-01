<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('toothinitial', function(Blueprint $table){

$table->integer('ToothInitialNum');

$table->integer('PatNum');

$table->string('ToothNum');

$table->integer('InitialType');

$table->string('Movement');

$table->text('DrawingSegment');

$table->integer('ColorDraw');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');

$table->string('DrawText');



});

}


public function down()
{
Schema::dropIfExists('toothinitial');
}

};
