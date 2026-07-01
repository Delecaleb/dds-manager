<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehraptobs', function(Blueprint $table){

$table->integer('EhrAptObsNum');

$table->integer('AptNum');

$table->integer('IdentifyingCode');

$table->integer('ValType');

$table->string('ValReported');

$table->string('UcumCode');

$table->string('ValCodeSystem');



});

}


public function down()
{
Schema::dropIfExists('ehraptobs');
}

};
