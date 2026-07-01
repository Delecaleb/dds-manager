<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('fee', function(Blueprint $table){

$table->integer('FeeNum');

$table->string('Amount');

$table->string('OldCode');

$table->integer('FeeSched');

$table->integer('UseDefaultFee');

$table->integer('UseDefaultCov');

$table->integer('CodeNum');

$table->integer('ClinicNum');

$table->integer('ProvNum');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->date('DateEffective');



});

}


public function down()
{
Schema::dropIfExists('fee');
}

};
