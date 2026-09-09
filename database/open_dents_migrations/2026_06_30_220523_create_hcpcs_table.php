<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('hcpcs', function (Blueprint $table) {

            $table->integer('HcpcsNum');

            $table->string('HcpcsCode');

            $table->string('DescriptionShort');

        });

    }

    public function down()
    {
        Schema::dropIfExists('hcpcs');
    }
};
