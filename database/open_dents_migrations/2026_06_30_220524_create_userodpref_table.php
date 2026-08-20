<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('userodpref', function (Blueprint $table) {

            $table->integer('UserOdPrefNum');

            $table->integer('UserNum');

            $table->integer('Fkey');

            $table->integer('FkeyType');

            $table->text('ValueString');

            $table->integer('ClinicNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('userodpref');
    }
};
