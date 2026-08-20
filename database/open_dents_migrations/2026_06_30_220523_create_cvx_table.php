<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('cvx', function (Blueprint $table) {

            $table->integer('CvxNum');

            $table->string('CvxCode');

            $table->string('Description');

            $table->string('IsActive');

        });

    }

    public function down()
    {
        Schema::dropIfExists('cvx');
    }
};
