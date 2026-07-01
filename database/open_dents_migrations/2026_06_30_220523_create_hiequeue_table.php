<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('hiequeue', function(Blueprint $table){

$table->integer('HieQueueNum');

$table->integer('PatNum');



});

}


public function down()
{
Schema::dropIfExists('hiequeue');
}

};
