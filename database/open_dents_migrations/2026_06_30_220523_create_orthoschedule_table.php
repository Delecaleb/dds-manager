<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthoschedule', function (Blueprint $table) {

            $table->integer('OrthoScheduleNum');

            $table->date('BandingDateOverride');

            $table->date('DebondDateOverride');

            $table->string('BandingAmount');

            $table->string('VisitAmount');

            $table->string('DebondAmount');

            $table->integer('IsActive');

            $table->string('SecDateTEdit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthoschedule');
    }
};
