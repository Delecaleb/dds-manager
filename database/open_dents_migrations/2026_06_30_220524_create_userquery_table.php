<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('userquery', function(Blueprint $table){

$table->integer('QueryNum');

$table->string('Description');

$table->string('FileName');

$table->text('QueryText');

$table->integer('IsReleased');

$table->integer('IsPromptSetup');

$table->integer('DefaultFormatRaw');



});

}


public function down()
{
Schema::dropIfExists('userquery');
}

};
