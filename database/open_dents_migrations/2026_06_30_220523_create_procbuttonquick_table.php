<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('procbuttonquick', function(Blueprint $table){

$table->integer('ProcButtonQuickNum');

$table->string('Description');

$table->string('CodeValue');

$table->string('Surf');

$table->integer('YPos');

$table->integer('ItemOrder');

$table->integer('IsLabel');



});

}


public function down()
{
Schema::dropIfExists('procbuttonquick');
}

};
