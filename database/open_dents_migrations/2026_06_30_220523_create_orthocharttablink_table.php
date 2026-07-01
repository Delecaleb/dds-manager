<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('orthocharttablink', function(Blueprint $table){

$table->integer('OrthoChartTabLinkNum');

$table->integer('ItemOrder');

$table->integer('OrthoChartTabNum');

$table->integer('DisplayFieldNum');

$table->integer('ColumnWidthOverride');



});

}


public function down()
{
Schema::dropIfExists('orthocharttablink');
}

};
