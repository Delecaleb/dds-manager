<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('toothgridcol', function(Blueprint $table){

$table->integer('ToothGridColNum');

$table->integer('SheetFieldNum');

$table->string('NameItem');

$table->integer('CellType');

$table->integer('ItemOrder');

$table->integer('ColumnWidth');

$table->integer('CodeNum');

$table->integer('ProcStatus');



});

}


public function down()
{
Schema::dropIfExists('toothgridcol');
}

};
