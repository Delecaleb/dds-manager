<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('eroutingactiondef', function(Blueprint $table){

$table->integer('ERoutingActionDefNum');

$table->integer('ERoutingDefNum');

$table->integer('ERoutingActionType');

$table->integer('ItemOrder');

$table->date('SecDateTEntry');

$table->date('DateTLastModified');

$table->integer('ForeignKeyType');

$table->integer('ForeignKey');

$table->string('LabelOverride');



});

}


public function down()
{
Schema::dropIfExists('eroutingactiondef');
}

};
