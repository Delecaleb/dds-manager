<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('guardian', function (Blueprint $table) {

            $table->integer('GuardianNum');

            $table->integer('PatNumChild');

            $table->integer('PatNumGuardian');

            $table->integer('Relationship');

            $table->integer('IsGuardian');

        });

    }

    public function down()
    {
        Schema::dropIfExists('guardian');
    }
};
