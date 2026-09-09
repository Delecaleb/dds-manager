<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('languageforeign', function (Blueprint $table) {

            $table->integer('LanguageForeignNum');

            $table->text('ClassType');

            $table->text('English');

            $table->string('Culture');

            $table->text('Translation');

            $table->text('Comments');

        });

    }

    public function down()
    {
        Schema::dropIfExists('languageforeign');
    }
};
