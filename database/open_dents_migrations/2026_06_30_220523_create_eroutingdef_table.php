<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('eroutingdef', function(Blueprint $table){

$table->integer('ERoutingDefNum');

$table->integer('ClinicNum');

$table->string('Description');

$table->integer('UserNumCreated');

$table->integer('UserNumModified');

$table->date('SecDateTEntered');

$table->date('DateLastModified');



});

}


public function down()
{
Schema::dropIfExists('eroutingdef');
}

};
