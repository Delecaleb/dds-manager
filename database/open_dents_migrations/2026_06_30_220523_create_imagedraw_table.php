<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('imagedraw', function (Blueprint $table) {

            $table->integer('ImageDrawNum');

            $table->integer('DocNum');

            $table->integer('MountNum');

            $table->integer('ColorDraw');

            $table->integer('ColorBack');

            $table->text('DrawingSegment');

            $table->string('DrawText');

            $table->string('FontSize');

            $table->integer('DrawType');

            $table->integer('ImageAnnotVendor');

            $table->text('Details');

            $table->integer('PearlLayer');

            $table->integer('BetterDiagLayer');

        });

    }

    public function down()
    {
        Schema::dropIfExists('imagedraw');
    }
};
