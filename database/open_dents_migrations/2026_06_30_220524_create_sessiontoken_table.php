<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sessiontoken', function(Blueprint $table){

$table->integer('SessionTokenNum');

$table->string('SessionTokenHash');

$table->date('Expiration');

$table->integer('TokenType');

$table->integer('FKey');



});

}


public function down()
{
Schema::dropIfExists('sessiontoken');
}

};
