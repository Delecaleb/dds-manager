<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailsecureattach', function (Blueprint $table) {

            $table->integer('EmailSecureAttachNum');

            $table->integer('ClinicNum');

            $table->integer('EmailAttachNum');

            $table->integer('EmailSecureNum');

            $table->string('AttachmentGuid');

            $table->string('DisplayedFileName');

            $table->string('Extension');

            $table->date('DateTEntry');

            $table->string('SecDateTEdit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailsecureattach');
    }
};
