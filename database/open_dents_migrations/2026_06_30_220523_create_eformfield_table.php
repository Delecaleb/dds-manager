<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eformfield', function (Blueprint $table) {

            $table->integer('EFormFieldNum');

            $table->integer('EFormNum');

            $table->integer('PatNum');

            $table->integer('FieldType');

            $table->string('DbLink');

            $table->text('ValueLabel');

            $table->text('ValueString');

            $table->integer('ItemOrder');

            $table->text('PickListVis');

            $table->text('PickListDb');

            $table->integer('IsHorizStacking');

            $table->integer('IsTextWrap');

            $table->integer('Width');

            $table->integer('FontScale');

            $table->integer('IsRequired');

            $table->string('ConditionalParent');

            $table->text('ConditionalValue');

            $table->integer('LabelAlign');

            $table->integer('SpaceBelow');

            $table->string('ReportableName');

            $table->integer('IsLocked');

            $table->integer('Border');

            $table->integer('IsWidthPercentage');

            $table->integer('MinWidth');

            $table->integer('WidthLabel');

            $table->integer('SpaceToRight');

            $table->integer('AutoImport');

            $table->integer('PrefillFromGuar');

            $table->text('ValueLabelEnglish');

            $table->text('PickListVisEnglish');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eformfield');
    }
};
