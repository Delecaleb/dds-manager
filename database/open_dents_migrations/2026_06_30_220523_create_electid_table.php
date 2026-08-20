<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('electid', function (Blueprint $table) {

            $table->integer('ElectIDNum');

            $table->string('PayorID');

            $table->string('CarrierName');

            $table->integer('IsMedicaid');

            $table->string('ProviderTypes');

            $table->text('Comments');

            $table->integer('CommBridge');

            $table->string('Attributes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('electid');
    }
};
