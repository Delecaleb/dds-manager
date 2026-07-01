<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('connectiongroup', function(Blueprint $table){

$table->integer('ConnectionGroupNum');

$table->string('Description');



});

}


public function down()
{
Schema::dropIfExists('connectiongroup');
}

};
