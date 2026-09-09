<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('displayfield', function (Blueprint $table) {

            $table->integer('DisplayFieldNum');

            $table->string('InternalName');

            $table->integer('ItemOrder');

            $table->string('Description');

            $table->integer('ColumnWidth');

            $table->integer('Category');

            $table->integer('ChartViewNum');

            $table->text('PickList');

            $table->string('DescriptionOverride');

        });

    }

    public function down()
    {
        Schema::dropIfExists('displayfield');
    }
};
