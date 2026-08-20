<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eform', function (Blueprint $table) {

            $table->integer('EFormNum');

            $table->integer('FormType');

            $table->integer('PatNum');

            $table->date('DateTimeShown');

            $table->string('Description');

            $table->date('DateTEdited');

            $table->integer('MaxWidth');

            $table->integer('EFormDefNum');

            $table->integer('Status');

            $table->integer('RevID');

            $table->integer('ShowLabelsBold');

            $table->integer('SpaceBelowEachField');

            $table->integer('SpaceToRightEachField');

            $table->integer('SaveImageCategory');

            $table->date('DateTimeSubmitted');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eform');
    }
};
