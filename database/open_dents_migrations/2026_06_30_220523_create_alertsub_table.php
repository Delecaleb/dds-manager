<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('alertsub', function (Blueprint $table) {

            $table->integer('AlertSubNum');

            $table->integer('UserNum');

            $table->integer('ClinicNum');

            $table->integer('Type');

            $table->integer('AlertCategoryNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('alertsub');
    }
};
