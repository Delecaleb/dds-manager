<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('taskunread', function(Blueprint $table){

$table->integer('TaskUnreadNum');

$table->integer('TaskNum');

$table->integer('UserNum');



});

}


public function down()
{
Schema::dropIfExists('taskunread');
}

};
