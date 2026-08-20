<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('languagepat', function (Blueprint $table) {

            $table->integer('LanguagePatNum');

            $table->string('PrefName');

            $table->string('Language');

            $table->text('Translation');

            $table->integer('EFormFieldDefNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('languagepat');
    }
};
