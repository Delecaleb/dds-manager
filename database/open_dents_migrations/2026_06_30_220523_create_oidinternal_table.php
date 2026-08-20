<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('oidinternal', function (Blueprint $table) {

            $table->integer('OIDInternalNum');

            $table->string('IDType');

            $table->string('IDRoot');

        });

    }

    public function down()
    {
        Schema::dropIfExists('oidinternal');
    }
};
