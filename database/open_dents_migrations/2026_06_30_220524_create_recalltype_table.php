<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('recalltype', function (Blueprint $table) {

            $table->integer('RecallTypeNum');

            $table->string('Description');

            $table->integer('DefaultInterval');

            $table->string('TimePattern');

            $table->string('Procedures');

            $table->integer('AppendToSpecial');

        });

    }

    public function down()
    {
        Schema::dropIfExists('recalltype');
    }
};
