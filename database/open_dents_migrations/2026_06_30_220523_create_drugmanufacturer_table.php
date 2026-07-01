<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('drugmanufacturer', function(Blueprint $table){

$table->integer('DrugManufacturerNum');

$table->string('ManufacturerName');

$table->string('ManufacturerCode');



});

}


public function down()
{
Schema::dropIfExists('drugmanufacturer');
}

};
