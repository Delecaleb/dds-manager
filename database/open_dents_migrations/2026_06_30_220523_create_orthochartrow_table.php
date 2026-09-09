<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthochartrow', function (Blueprint $table) {

            $table->integer('OrthoChartRowNum');

            $table->integer('PatNum');

            $table->date('DateTimeService');

            $table->integer('UserNum');

            $table->integer('ProvNum');

            $table->text('Signature');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthochartrow');
    }
};
