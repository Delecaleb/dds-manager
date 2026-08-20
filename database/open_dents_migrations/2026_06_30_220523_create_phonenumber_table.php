<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('phonenumber', function (Blueprint $table) {

            $table->integer('PhoneNumberNum');

            $table->integer('PatNum');

            $table->string('PhoneNumberVal');

            $table->string('PhoneNumberDigits');

            $table->integer('PhoneType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('phonenumber');
    }
};
