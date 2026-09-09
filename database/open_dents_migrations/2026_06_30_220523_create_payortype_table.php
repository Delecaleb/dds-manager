<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('payortype', function (Blueprint $table) {

            $table->integer('PayorTypeNum');

            $table->integer('PatNum');

            $table->date('DateStart');

            $table->string('SopCode');

            $table->text('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('payortype');
    }
};
