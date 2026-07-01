<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('eclipboardsheetdef', function(Blueprint $table){

$table->integer('EClipboardSheetDefNum');

$table->integer('SheetDefNum');

$table->integer('ClinicNum');

$table->integer('ResubmitInterval');

$table->integer('ItemOrder');

$table->integer('PrefillStatus');

$table->integer('MinAge');

$table->integer('MaxAge');

$table->text('IgnoreSheetDefNums');

$table->integer('PrefillStatusOverride');

$table->integer('EFormDefNum');

$table->integer('Frequency');

$table->string('SheetDefNumsConsidered');



});

}


public function down()
{
Schema::dropIfExists('eclipboardsheetdef');
}

};
