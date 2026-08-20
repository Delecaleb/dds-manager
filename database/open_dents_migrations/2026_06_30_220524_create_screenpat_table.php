<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('screenpat', function (Blueprint $table) {

            $table->integer('ScreenPatNum');

            $table->integer('PatNum');

            $table->integer('ScreenGroupNum');

            $table->integer('SheetNum');

            $table->integer('PatScreenPerm');

        });

    }

    public function down()
    {
        Schema::dropIfExists('screenpat');
    }
};
