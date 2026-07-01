<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('statementprod', function(Blueprint $table){

$table->integer('StatementProdNum');

$table->integer('StatementNum');

$table->integer('FKey');

$table->integer('ProdType');

$table->integer('LateChargeAdjNum');

$table->integer('DocNum');



});

}


public function down()
{
Schema::dropIfExists('statementprod');
}

};
