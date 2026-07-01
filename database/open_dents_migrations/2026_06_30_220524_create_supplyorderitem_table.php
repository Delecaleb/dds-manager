<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('supplyorderitem', function(Blueprint $table){

$table->integer('SupplyOrderItemNum');

$table->integer('SupplyOrderNum');

$table->integer('SupplyNum');

$table->integer('Qty');

$table->string('Price');

$table->date('DateReceived');



});

}


public function down()
{
Schema::dropIfExists('supplyorderitem');
}

};
