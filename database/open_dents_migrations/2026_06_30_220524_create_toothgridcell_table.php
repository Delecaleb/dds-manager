<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('toothgridcell', function (Blueprint $table) {

            $table->integer('ToothGridCellNum');

            $table->integer('SheetFieldNum');

            $table->integer('ToothGridColNum');

            $table->string('ValueEntered');

            $table->string('ToothNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('toothgridcell');
    }
};
