<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('userclinic', function (Blueprint $table) {

            $table->integer('UserClinicNum');

            $table->integer('UserNum');

            $table->integer('ClinicNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('userclinic');
    }
};
