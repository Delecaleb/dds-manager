<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sheet', function(Blueprint $table){

$table->integer('SheetNum');

$table->integer('SheetType');

$table->integer('PatNum');

$table->date('DateTimeSheet');

$table->string('FontSize');

$table->string('FontName');

$table->integer('Width');

$table->integer('Height');

$table->integer('IsLandscape');

$table->text('InternalNote');

$table->string('Description');

$table->integer('ShowInTerminal');

$table->integer('IsWebForm');

$table->integer('IsMultiPage');

$table->integer('IsDeleted');

$table->integer('SheetDefNum');

$table->integer('DocNum');

$table->integer('ClinicNum');

$table->date('DateTSheetEdited');

$table->integer('HasMobileLayout');

$table->integer('RevID');

$table->integer('WebFormSheetID');

$table->date('DateTimeSubmitted');



});

}


public function down()
{
Schema::dropIfExists('sheet');
}

};
