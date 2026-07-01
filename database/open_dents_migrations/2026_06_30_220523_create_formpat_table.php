<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('formpat', function(Blueprint $table){

$table->integer('FormPatNum');

$table->integer('PatNum');

$table->date('FormDateTime');



});

}


public function down()
{
Schema::dropIfExists('formpat');
}

};
