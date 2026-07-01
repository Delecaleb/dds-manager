<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sheetdef', function(Blueprint $table){

$table->integer('SheetDefNum');

$table->string('Description');

$table->integer('SheetType');

$table->string('FontSize');

$table->string('FontName');

$table->integer('Width');

$table->integer('Height');

$table->integer('IsLandscape');

$table->integer('PageCount');

$table->integer('IsMultiPage');

$table->integer('BypassGlobalLock');

$table->integer('HasMobileLayout');

$table->date('DateTCreated');

$table->integer('RevID');

$table->integer('AutoCheckSaveImage');

$table->integer('AutoCheckSaveImageDocCategory');



});

}


public function down()
{
Schema::dropIfExists('sheetdef');
}

};
