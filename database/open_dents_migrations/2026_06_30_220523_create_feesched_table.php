<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('feesched', function(Blueprint $table){

$table->integer('FeeSchedNum');

$table->string('Description');

$table->integer('FeeSchedType');

$table->integer('ItemOrder');

$table->integer('IsHidden');

$table->integer('IsGlobal');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');



});

}


public function down()
{
Schema::dropIfExists('feesched');
}

};
