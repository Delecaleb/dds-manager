<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patfieldpickitem', function(Blueprint $table){

$table->integer('PatFieldPickItemNum');

$table->integer('PatFieldDefNum');

$table->string('Name');

$table->string('Abbreviation');

$table->integer('IsHidden');

$table->integer('ItemOrder');



});

}


public function down()
{
Schema::dropIfExists('patfieldpickitem');
}

};
