<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('mountitemdef', function(Blueprint $table){

$table->integer('MountItemDefNum');

$table->integer('MountDefNum');

$table->integer('Xpos');

$table->integer('Ypos');

$table->integer('Width');

$table->integer('Height');

$table->integer('ItemOrder');

$table->integer('RotateOnAcquire');

$table->string('ToothNumbers');

$table->text('TextShowing');

$table->string('FontSize');



});

}


public function down()
{
Schema::dropIfExists('mountitemdef');
}

};
