<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailtemplate', function (Blueprint $table) {

            $table->integer('EmailTemplateNum');

            $table->text('Subject');

            $table->text('BodyText');

            $table->text('Description');

            $table->integer('TemplateType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailtemplate');
    }
};
