<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('replicationserver', function(Blueprint $table){

$table->integer('ReplicationServerNum');

$table->text('Descript');

$table->integer('ServerId');

$table->integer('RangeStart');

$table->integer('RangeEnd');

$table->string('AtoZpath');

$table->integer('UpdateBlocked');

$table->string('SlaveMonitor');



});

}


public function down()
{
Schema::dropIfExists('replicationserver');
}

};
