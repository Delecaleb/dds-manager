<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('alertread', function(Blueprint $table){

$table->integer('AlertReadNum');

$table->integer('AlertItemNum');

$table->integer('UserNum');



});

}


public function down()
{
Schema::dropIfExists('alertread');
}

};
