<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('payterminal', function (Blueprint $table) {

            $table->integer('PayTerminalNum');

            $table->string('Name');

            $table->integer('ClinicNum');

            $table->string('TerminalID');

            $table->string('CCIntegration');

        });

    }

    public function down()
    {
        Schema::dropIfExists('payterminal');
    }
};
