<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('icd9', function (Blueprint $table) {

            $table->integer('ICD9Num');

            $table->string('ICD9Code');

            $table->string('Description');

            $table->string('DateTStamp');

        });

    }

    public function down()
    {
        Schema::dropIfExists('icd9');
    }
};
