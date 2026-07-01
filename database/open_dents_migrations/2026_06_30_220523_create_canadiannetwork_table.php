<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('canadiannetwork', function(Blueprint $table){

$table->integer('CanadianNetworkNum');

$table->string('Abbrev');

$table->string('Descript');

$table->string('CanadianTransactionPrefix');

$table->integer('CanadianIsRprHandler');



});

}


public function down()
{
Schema::dropIfExists('canadiannetwork');
}

};
