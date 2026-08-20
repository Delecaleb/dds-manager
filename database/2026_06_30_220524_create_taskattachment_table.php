<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('taskattachment', function (Blueprint $table) {

            $table->integer('TaskAttachmentNum');

            $table->integer('TaskNum');

            $table->integer('DocNum');

            $table->text('TextValue');

            $table->string('Description');

        });

    }

    public function down()
    {
        Schema::dropIfExists('taskattachment');
    }
};
