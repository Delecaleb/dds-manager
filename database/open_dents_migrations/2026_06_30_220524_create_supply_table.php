<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('supply', function(Blueprint $table){

$table->integer('SupplyNum');

$table->integer('SupplierNum');

$table->string('CatalogNumber');

$table->string('Descript');

$table->integer('Category');

$table->integer('ItemOrder');

$table->string('LevelDesired');

$table->integer('IsHidden');

$table->string('Price');

$table->string('BarCodeOrID');

$table->string('DispDefaultQuant');

$table->integer('DispUnitsCount');

$table->string('DispUnitDesc');

$table->string('LevelOnHand');

$table->integer('OrderQty');



});

}


public function down()
{
Schema::dropIfExists('supply');
}

};
