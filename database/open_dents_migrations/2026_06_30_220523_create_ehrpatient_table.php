<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrpatient', function (Blueprint $table) {

            $table->integer('PatNum');

            $table->string('MotherMaidenFname');

            $table->string('MotherMaidenLname');

            $table->integer('VacShareOk');

            $table->string('MedicaidState');

            $table->string('SexualOrientation');

            $table->string('GenderIdentity');

            $table->string('SexualOrientationNote');

            $table->string('GenderIdentityNote');

            $table->date('DischargeDate');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrpatient');
    }
};
