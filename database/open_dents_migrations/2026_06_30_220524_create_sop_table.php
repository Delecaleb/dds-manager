<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sop', function(Blueprint $table){

$table->integer('SopNum');

$table->string('SopCode');

$table->string('Description');



});

}


public function down()
{
Schema::dropIfExists('sop');
}

};
