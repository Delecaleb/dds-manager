<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('site', function (Blueprint $table) {

            $table->integer('SiteNum');

            $table->string('Description');

            $table->text('Note');

            $table->string('Address');

            $table->string('Address2');

            $table->string('City');

            $table->string('State');

            $table->string('Zip');

            $table->integer('ProvNum');

            $table->integer('PlaceService');

        });

    }

    public function down()
    {
        Schema::dropIfExists('site');
    }
};
