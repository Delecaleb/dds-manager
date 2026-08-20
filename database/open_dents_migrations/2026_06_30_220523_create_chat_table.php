<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('chat', function (Blueprint $table) {

            $table->integer('ChatNum');

            $table->string('Name');

        });

    }

    public function down()
    {
        Schema::dropIfExists('chat');
    }
};
