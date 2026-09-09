<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mountdef', function (Blueprint $table) {

            $table->integer('MountDefNum');

            $table->string('Description');

            $table->integer('ItemOrder');

            $table->integer('Width');

            $table->integer('Height');

            $table->integer('ColorBack');

            $table->integer('ColorFore');

            $table->integer('ColorTextBack');

            $table->string('ScaleValue');

            $table->integer('DefaultCat');

            $table->integer('FlipOnAcquire');

            $table->integer('AdjModeAfterSeries');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mountdef');
    }
};
