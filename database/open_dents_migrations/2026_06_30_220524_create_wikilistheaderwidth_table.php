<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('wikilistheaderwidth', function (Blueprint $table) {

            $table->integer('WikiListHeaderWidthNum');

            $table->string('ListName');

            $table->string('ColName');

            $table->integer('ColWidth');

            $table->text('PickList');

            $table->integer('IsHidden');

        });

    }

    public function down()
    {
        Schema::dropIfExists('wikilistheaderwidth');
    }
};
