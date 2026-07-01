<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('evaluationcriteriondef', function(Blueprint $table){

$table->integer('EvaluationCriterionDefNum');

$table->integer('EvaluationDefNum');

$table->string('CriterionDescript');

$table->integer('IsCategoryName');

$table->integer('GradingScaleNum');

$table->integer('ItemOrder');

$table->string('MaxPointsPoss');



});

}


public function down()
{
Schema::dropIfExists('evaluationcriteriondef');
}

};
