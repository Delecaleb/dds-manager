<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('dictcustom', function(Blueprint $table){

$table->integer('DictCustomNum');

$table->string('WordText');



});

}


public function down()
{
Schema::dropIfExists('dictcustom');
}

};
