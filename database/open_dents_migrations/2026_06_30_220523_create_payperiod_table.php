<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('payperiod', function(Blueprint $table){

$table->integer('PayPeriodNum');

$table->date('DateStart');

$table->date('DateStop');

$table->date('DatePaycheck');



});

}


public function down()
{
Schema::dropIfExists('payperiod');
}

};
