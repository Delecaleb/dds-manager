<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('securitylog', function (Blueprint $table) {

            $table->integer('SecurityLogNum');

            $table->integer('PermType');

            $table->integer('UserNum');

            $table->date('LogDateTime');

            $table->text('LogText');

            $table->integer('PatNum');

            $table->string('CompName');

            $table->integer('FKey');

            $table->integer('LogSource');

            $table->integer('DefNum');

            $table->integer('DefNumError');

            $table->date('DateTPrevious');

        });

    }

    public function down()
    {
        Schema::dropIfExists('securitylog');
    }
};
