<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('intervention', function (Blueprint $table) {

            $table->integer('InterventionNum');

            $table->integer('PatNum');

            $table->integer('ProvNum');

            $table->string('CodeValue');

            $table->string('CodeSystem');

            $table->text('Note');

            $table->date('DateEntry');

            $table->integer('CodeSet');

            $table->integer('IsPatDeclined');

        });

    }

    public function down()
    {
        Schema::dropIfExists('intervention');
    }
};
