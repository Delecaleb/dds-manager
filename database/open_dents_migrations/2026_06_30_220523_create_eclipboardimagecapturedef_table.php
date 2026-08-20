<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eclipboardimagecapturedef', function (Blueprint $table) {

            $table->integer('EClipboardImageCaptureDefNum');

            $table->integer('DefNum');

            $table->integer('IsSelfPortrait');

            $table->integer('FrequencyDays');

            $table->integer('ClinicNum');

            $table->integer('OcrCaptureType');

            $table->integer('Frequency');

            $table->integer('ResubmitInterval');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eclipboardimagecapturedef');
    }
};
