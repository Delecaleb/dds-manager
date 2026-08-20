<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailautograph', function (Blueprint $table) {

            $table->integer('EmailAutographNum');

            $table->text('Description');

            $table->string('EmailAddress');

            $table->text('AutographText');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailautograph');
    }
};
