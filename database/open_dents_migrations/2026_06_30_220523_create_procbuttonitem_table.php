<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('procbuttonitem', function(Blueprint $table){

$table->integer('ProcButtonItemNum');

$table->integer('ProcButtonNum');

$table->string('OldCode');

$table->integer('AutoCodeNum');

$table->integer('CodeNum');

$table->integer('ItemOrder');



});

}


public function down()
{
Schema::dropIfExists('procbuttonitem');
}

};
