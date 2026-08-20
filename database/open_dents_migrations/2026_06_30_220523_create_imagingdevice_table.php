<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('imagingdevice', function (Blueprint $table) {

            $table->integer('ImagingDeviceNum');

            $table->string('Description');

            $table->string('ComputerName');

            $table->integer('DeviceType');

            $table->string('TwainName');

            $table->integer('ItemOrder');

            $table->integer('ShowTwainUI');

        });

    }

    public function down()
    {
        Schema::dropIfExists('imagingdevice');
    }
};
