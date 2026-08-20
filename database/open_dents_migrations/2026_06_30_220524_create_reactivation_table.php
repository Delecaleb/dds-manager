<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('reactivation', function (Blueprint $table) {

            $table->integer('ReactivationNum');

            $table->integer('PatNum');

            $table->integer('ReactivationStatus');

            $table->text('ReactivationNote');

            $table->integer('DoNotContact');

        });

    }

    public function down()
    {
        Schema::dropIfExists('reactivation');
    }
};
