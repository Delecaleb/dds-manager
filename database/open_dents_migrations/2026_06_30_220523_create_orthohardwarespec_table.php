<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('orthohardwarespec', function(Blueprint $table){

$table->integer('OrthoHardwareSpecNum');

$table->integer('OrthoHardwareType');

$table->string('Description');

$table->integer('ItemColor');

$table->integer('IsHidden');

$table->integer('ItemOrder');



});

}


public function down()
{
Schema::dropIfExists('orthohardwarespec');
}

};
