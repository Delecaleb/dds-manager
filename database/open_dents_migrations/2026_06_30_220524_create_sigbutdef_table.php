<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sigbutdef', function(Blueprint $table){

$table->integer('SigButDefNum');

$table->string('ButtonText');

$table->integer('ButtonIndex');

$table->integer('SynchIcon');

$table->string('ComputerName');

$table->integer('SigElementDefNumUser');

$table->integer('SigElementDefNumExtra');

$table->integer('SigElementDefNumMsg');



});

}


public function down()
{
Schema::dropIfExists('sigbutdef');
}

};
