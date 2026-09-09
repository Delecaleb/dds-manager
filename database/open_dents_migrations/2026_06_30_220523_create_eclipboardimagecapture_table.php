<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eclipboardimagecapture', function (Blueprint $table) {

            $table->integer('EClipboardImageCaptureNum');

            $table->integer('PatNum');

            $table->integer('DefNum');

            $table->integer('IsSelfPortrait');

            $table->date('DateTimeUpserted');

            $table->integer('DocNum');

            $table->integer('OcrCaptureType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eclipboardimagecapture');
    }
};
