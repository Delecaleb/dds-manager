<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthochart', function (Blueprint $table) {

            $table->integer('OrthoChartNum');

            $table->integer('PatNum');

            $table->date('DateService');

            $table->string('FieldName');

            $table->text('FieldValue');

            $table->integer('UserNum');

            $table->integer('ProvNum');

            $table->integer('OrthoChartRowNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthochart');
    }
};
