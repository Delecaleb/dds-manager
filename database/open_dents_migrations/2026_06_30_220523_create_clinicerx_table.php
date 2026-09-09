<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('clinicerx', function (Blueprint $table) {

            $table->integer('ClinicErxNum');

            $table->integer('PatNum');

            $table->string('ClinicDesc');

            $table->integer('ClinicNum');

            $table->integer('EnabledStatus');

            $table->string('ClinicId');

            $table->string('ClinicKey');

            $table->string('AccountId');

            $table->integer('RegistrationKeyNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('clinicerx');
    }
};
