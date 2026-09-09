<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('contact', function (Blueprint $table) {

            $table->integer('ContactNum');

            $table->string('LName');

            $table->string('FName');

            $table->string('WkPhone');

            $table->string('Fax');

            $table->integer('Category');

            $table->text('Notes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('contact');
    }
};
