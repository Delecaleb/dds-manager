<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('providerident', function(Blueprint $table){

$table->integer('ProviderIdentNum');

$table->integer('ProvNum');

$table->string('PayorID');

$table->integer('SuppIDType');

$table->string('IDNumber');



});

}


public function down()
{
Schema::dropIfExists('providerident');
}

};
