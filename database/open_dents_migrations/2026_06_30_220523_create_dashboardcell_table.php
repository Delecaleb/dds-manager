<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('dashboardcell', function(Blueprint $table){

$table->integer('DashboardCellNum');

$table->integer('DashboardLayoutNum');

$table->integer('CellRow');

$table->integer('CellColumn');

$table->string('CellType');

$table->text('CellSettings');

$table->date('LastQueryTime');

$table->text('LastQueryData');

$table->integer('RefreshRateSeconds');



});

}


public function down()
{
Schema::dropIfExists('dashboardcell');
}

};
