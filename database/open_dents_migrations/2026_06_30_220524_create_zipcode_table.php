<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('zipcode', function (Blueprint $table) {

            $table->integer('ZipCodeNum');

            $table->string('ZipCodeDigits');

            $table->string('City');

            $table->string('State');

            $table->integer('IsFrequent');

        });

    }

    public function down()
    {
        Schema::dropIfExists('zipcode');
    }
};
