<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('displayreport', function(Blueprint $table){

$table->integer('DisplayReportNum');

$table->string('InternalName');

$table->integer('ItemOrder');

$table->string('Description');

$table->integer('Category');

$table->integer('IsHidden');

$table->integer('IsVisibleInSubMenu');



});

}


public function down()
{
Schema::dropIfExists('displayreport');
}

};
