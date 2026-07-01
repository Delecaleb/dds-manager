<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('definition', function(Blueprint $table){

$table->integer('DefNum');

$table->integer('Category');

$table->integer('ItemOrder');

$table->string('ItemName');

$table->string('ItemValue');

$table->integer('ItemColor');

$table->integer('IsHidden');

$table->string('Supp');



});

}


public function down()
{
Schema::dropIfExists('definition');
}

};
