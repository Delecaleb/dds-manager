<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('sheetfielddef', function(Blueprint $table){

$table->integer('SheetFieldDefNum');

$table->integer('SheetDefNum');

$table->integer('FieldType');

$table->string('FieldName');

$table->text('FieldValue');

$table->string('FontSize');

$table->string('FontName');

$table->integer('FontIsBold');

$table->integer('XPos');

$table->integer('YPos');

$table->integer('Width');

$table->integer('Height');

$table->integer('GrowthBehavior');

$table->string('RadioButtonValue');

$table->string('RadioButtonGroup');

$table->integer('IsRequired');

$table->integer('TabOrder');

$table->string('ReportableName');

$table->integer('TextAlign');

$table->integer('IsPaymentOption');

$table->integer('ItemColor');

$table->integer('IsLocked');

$table->integer('TabOrderMobile');

$table->text('UiLabelMobile');

$table->text('UiLabelMobileRadioButton');

$table->integer('LayoutMode');

$table->string('Language');

$table->integer('CanElectronicallySign');

$table->integer('IsSigProvRestricted');



});

}


public function down()
{
Schema::dropIfExists('sheetfielddef');
}

};
