<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('conngroupattach', function (Blueprint $table) {

            $table->integer('ConnGroupAttachNum');

            $table->integer('ConnectionGroupNum');

            $table->integer('CentralConnectionNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('conngroupattach');
    }
};
