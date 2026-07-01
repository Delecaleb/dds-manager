<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('county', function(Blueprint $table){

$table->integer('CountyNum');

$table->string('CountyName');

$table->string('CountyCode');



});

}


public function down()
{
Schema::dropIfExists('county');
}

};
