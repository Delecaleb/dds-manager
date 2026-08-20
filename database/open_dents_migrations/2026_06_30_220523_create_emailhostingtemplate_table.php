<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailhostingtemplate', function (Blueprint $table) {

            $table->integer('EmailHostingTemplateNum');

            $table->string('TemplateName');

            $table->text('Subject');

            $table->text('BodyPlainText');

            $table->text('BodyHTML');

            $table->integer('TemplateId');

            $table->integer('ClinicNum');

            $table->string('EmailTemplateType');

            $table->string('TemplateType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailhostingtemplate');
    }
};
