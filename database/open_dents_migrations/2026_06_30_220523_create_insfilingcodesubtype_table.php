<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('insfilingcodesubtype', function (Blueprint $table) {

            $table->integer('InsFilingCodeSubtypeNum');

            $table->integer('InsFilingCodeNum');

            $table->string('Descript');

        });

    }

    public function down()
    {
        Schema::dropIfExists('insfilingcodesubtype');
    }
};
