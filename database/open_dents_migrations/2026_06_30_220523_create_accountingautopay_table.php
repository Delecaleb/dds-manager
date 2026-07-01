<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('accountingautopay', function(Blueprint $table){

$table->integer('AccountingAutoPayNum');

$table->integer('PayType');

$table->string('PickList');



});

}


public function down()
{
Schema::dropIfExists('accountingautopay');
}

};
