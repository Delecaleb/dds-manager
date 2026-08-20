<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('sheetfield', function (Blueprint $table) {

            $table->integer('SheetFieldNum');

            $table->integer('SheetNum');

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

            $table->integer('ItemColor');

            $table->date('DateTimeSig');

            $table->integer('IsLocked');

            $table->integer('TabOrderMobile');

            $table->text('UiLabelMobile');

            $table->text('UiLabelMobileRadioButton');

            $table->integer('SheetFieldDefNum');

            $table->integer('CanElectronicallySign');

            $table->integer('IsSigProvRestricted');

            $table->integer('UserSigned');

        });

    }

    public function down()
    {
        Schema::dropIfExists('sheetfield');
    }
};
