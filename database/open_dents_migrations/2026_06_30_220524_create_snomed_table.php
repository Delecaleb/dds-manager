<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('snomed', function (Blueprint $table) {

            $table->integer('SnomedNum');

            $table->string('SnomedCode');

            $table->string('Description');

        });

    }

    public function down()
    {
        Schema::dropIfExists('snomed');
    }
};
