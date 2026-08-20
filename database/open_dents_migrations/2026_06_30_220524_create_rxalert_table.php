<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('rxalert', function (Blueprint $table) {

            $table->integer('RxAlertNum');

            $table->integer('RxDefNum');

            $table->integer('DiseaseDefNum');

            $table->integer('AllergyDefNum');

            $table->integer('MedicationNum');

            $table->string('NotificationMsg');

            $table->integer('IsHighSignificance');

        });

    }

    public function down()
    {
        Schema::dropIfExists('rxalert');
    }
};
