<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlabimage', function(Blueprint $table){

$table->integer('EhrLabImageNum');

$table->integer('EhrLabNum');

$table->integer('DocNum');



});

}


public function down()
{
Schema::dropIfExists('ehrlabimage');
}

};
