<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('procmultivisit', function(Blueprint $table){

$table->integer('ProcMultiVisitNum');

$table->integer('GroupProcMultiVisitNum');

$table->integer('ProcNum');

$table->integer('ProcStatus');

$table->integer('IsInProcess');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');

$table->integer('PatNum');



});

}


public function down()
{
Schema::dropIfExists('procmultivisit');
}

};
