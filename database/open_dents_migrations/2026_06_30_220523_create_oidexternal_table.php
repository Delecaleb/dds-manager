<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('oidexternal', function(Blueprint $table){

$table->integer('OIDExternalNum');

$table->string('IDType');

$table->integer('IDInternal');

$table->string('IDExternal');

$table->string('rootExternal');



});

}


public function down()
{
Schema::dropIfExists('oidexternal');
}

};
