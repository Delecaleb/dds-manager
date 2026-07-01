<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('dbmlog', function(Blueprint $table){

$table->integer('DbmLogNum');

$table->integer('UserNum');

$table->integer('FKey');

$table->integer('FKeyType');

$table->integer('ActionType');

$table->date('DateTimeEntry');

$table->string('MethodName');

$table->text('LogText');



});

}


public function down()
{
Schema::dropIfExists('dbmlog');
}

};
