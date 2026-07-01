<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('dashboardar', function(Blueprint $table){

$table->integer('DashboardARNum');

$table->date('DateCalc');

$table->string('BalTotal');

$table->string('InsEst');



});

}


public function down()
{
Schema::dropIfExists('dashboardar');
}

};
