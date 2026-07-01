<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sigelementdef', function(Blueprint $table){

$table->integer('SigElementDefNum');

$table->integer('LightRow');

$table->integer('LightColor');

$table->integer('SigElementType');

$table->string('SigText');

$table->text('Sound');

$table->integer('ItemOrder');



});

}


public function down()
{
Schema::dropIfExists('sigelementdef');
}

};
