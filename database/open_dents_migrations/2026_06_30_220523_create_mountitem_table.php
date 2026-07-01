<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('mountitem', function(Blueprint $table){

$table->integer('MountItemNum');

$table->integer('MountNum');

$table->integer('Xpos');

$table->integer('Ypos');

$table->integer('ItemOrder');

$table->integer('Width');

$table->integer('Height');

$table->integer('RotateOnAcquire');

$table->string('ToothNumbers');

$table->text('TextShowing');

$table->string('FontSize');



});

}


public function down()
{
Schema::dropIfExists('mountitem');
}

};
