<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('recalltrigger', function(Blueprint $table){

$table->integer('RecallTriggerNum');

$table->integer('RecallTypeNum');

$table->integer('CodeNum');



});

}


public function down()
{
Schema::dropIfExists('recalltrigger');
}

};
