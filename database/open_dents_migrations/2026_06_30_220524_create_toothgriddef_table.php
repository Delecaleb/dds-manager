<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('toothgriddef', function(Blueprint $table){

$table->integer('ToothGridDefNum');

$table->string('NameInternal');

$table->string('NameShowing');

$table->integer('CellType');

$table->integer('ItemOrder');

$table->integer('ColumnWidth');

$table->integer('CodeNum');

$table->integer('ProcStatus');

$table->integer('SheetFieldDefNum');



});

}


public function down()
{
Schema::dropIfExists('toothgriddef');
}

};
