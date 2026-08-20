<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('appointmentrule', function (Blueprint $table) {

            $table->integer('AppointmentRuleNum');

            $table->string('RuleDesc');

            $table->string('CodeStart');

            $table->string('CodeEnd');

            $table->integer('IsEnabled');

        });

    }

    public function down()
    {
        Schema::dropIfExists('appointmentrule');
    }
};
