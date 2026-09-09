<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('userweb', function (Blueprint $table) {

            $table->integer('UserWebNum');

            $table->integer('FKey');

            $table->integer('FKeyType');

            $table->string('UserName');

            $table->string('Password');

            $table->string('PasswordResetCode');

            $table->integer('RequireUserNameChange');

            $table->date('DateTimeLastLogin');

            $table->integer('RequirePasswordChange');

        });

    }

    public function down()
    {
        Schema::dropIfExists('userweb');
    }
};
