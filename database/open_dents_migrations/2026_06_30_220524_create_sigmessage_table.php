<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sigmessage', function(Blueprint $table){

$table->integer('SigMessageNum');

$table->string('ButtonText');

$table->integer('ButtonIndex');

$table->integer('SynchIcon');

$table->string('FromUser');

$table->string('ToUser');

$table->date('MessageDateTime');

$table->date('AckDateTime');

$table->string('SigText');

$table->integer('SigElementDefNumUser');

$table->integer('SigElementDefNumExtra');

$table->integer('SigElementDefNumMsg');



});

}


public function down()
{
Schema::dropIfExists('sigmessage');
}

};
