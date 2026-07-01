<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('toolbutitem', function(Blueprint $table){

$table->integer('ToolButItemNum');

$table->integer('ProgramNum');

$table->integer('ToolBar');

$table->string('ButtonText');



});

}


public function down()
{
Schema::dropIfExists('toolbutitem');
}

};
