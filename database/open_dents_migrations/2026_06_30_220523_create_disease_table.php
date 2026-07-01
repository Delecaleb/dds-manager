<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('disease', function(Blueprint $table){

$table->integer('DiseaseNum');

$table->integer('PatNum');

$table->integer('DiseaseDefNum');

$table->text('PatNote');

$table->string('DateTStamp');

$table->integer('ProbStatus');

$table->date('DateStart');

$table->date('DateStop');

$table->string('SnomedProblemType');

$table->integer('FunctionStatus');



});

}


public function down()
{
Schema::dropIfExists('disease');
}

};
