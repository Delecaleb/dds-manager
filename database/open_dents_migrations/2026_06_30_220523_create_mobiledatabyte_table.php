<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mobiledatabyte', function (Blueprint $table) {

            $table->integer('MobileDataByteNum');

            $table->text('RawBase64Data');

            $table->text('RawBase64Code');

            $table->text('RawBase64Tag');

            $table->integer('PatNum');

            $table->integer('ActionType');

            $table->date('DateTimeEntry');

            $table->date('DateTimeExpires');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mobiledatabyte');
    }
};
