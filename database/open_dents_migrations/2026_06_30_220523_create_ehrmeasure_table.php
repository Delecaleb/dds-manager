<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrmeasure', function(Blueprint $table){

$table->integer('EhrMeasureNum');

$table->integer('MeasureType');

$table->integer('Numerator');

$table->integer('Denominator');



});

}


public function down()
{
Schema::dropIfExists('ehrmeasure');
}

};
