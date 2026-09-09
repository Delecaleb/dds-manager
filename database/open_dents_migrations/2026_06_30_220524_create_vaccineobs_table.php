<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('vaccineobs', function (Blueprint $table) {

            $table->integer('VaccineObsNum');

            $table->integer('VaccinePatNum');

            $table->integer('ValType');

            $table->integer('IdentifyingCode');

            $table->string('ValReported');

            $table->integer('ValCodeSystem');

            $table->integer('VaccineObsNumGroup');

            $table->string('UcumCode');

            $table->date('DateObs');

            $table->string('MethodCode');

        });

    }

    public function down()
    {
        Schema::dropIfExists('vaccineobs');
    }
};
