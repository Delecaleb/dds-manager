<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mobileappdevice', function (Blueprint $table) {

            $table->integer('MobileAppDeviceNum');

            $table->integer('ClinicNum');

            $table->string('DeviceName');

            $table->string('UniqueID');

            $table->integer('IsEclipboardEnabled');

            $table->date('EclipboardLastAttempt');

            $table->date('EclipboardLastLogin');

            $table->integer('PatNum');

            $table->date('LastCheckInActivity');

            $table->integer('IsBYODDevice');

            $table->integer('DevicePage');

            $table->integer('UserNum');

            $table->integer('IsODTouchEnabled');

            $table->date('ODTouchLastLogin');

            $table->date('ODTouchLastAttempt');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mobileappdevice');
    }
};
