<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('drugunit', function(Blueprint $table){

$table->integer('DrugUnitNum');

$table->string('UnitIdentifier');

$table->string('UnitText');



});

}


public function down()
{
Schema::dropIfExists('drugunit');
}

};
