<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mobilebrandingprofile', function (Blueprint $table) {

            $table->integer('MobileBrandingProfileNum');

            $table->integer('ClinicNum');

            $table->string('OfficeDescription');

            $table->string('LogoFilePath');

            $table->string('DateTStamp');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mobilebrandingprofile');
    }
};
