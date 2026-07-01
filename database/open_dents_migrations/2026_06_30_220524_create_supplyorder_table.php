<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('supplyorder', function(Blueprint $table){

$table->integer('SupplyOrderNum');

$table->integer('SupplierNum');

$table->date('DatePlaced');

$table->text('Note');

$table->string('AmountTotal');

$table->integer('UserNum');

$table->string('ShippingCharge');

$table->date('DateReceived');



});

}


public function down()
{
Schema::dropIfExists('supplyorder');
}

};
