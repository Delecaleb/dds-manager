<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('insfilingcode', function (Blueprint $table) {

            $table->integer('InsFilingCodeNum');

            $table->string('Descript');

            $table->string('EclaimCode');

            $table->integer('ItemOrder');

            $table->integer('GroupType');

            $table->integer('ExcludeOtherCoverageOnPriClaims');

        });

    }

    public function down()
    {
        Schema::dropIfExists('insfilingcode');
    }
};
