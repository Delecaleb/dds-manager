<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('labturnaround', function (Blueprint $table) {

            $table->integer('LabTurnaroundNum');

            $table->integer('LaboratoryNum');

            $table->string('Description');

            $table->integer('DaysPublished');

            $table->integer('DaysActual');

        });

    }

    public function down()
    {
        Schema::dropIfExists('labturnaround');
    }
};
