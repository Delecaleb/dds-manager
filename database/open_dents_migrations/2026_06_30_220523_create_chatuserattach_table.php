<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('chatuserattach', function (Blueprint $table) {

            $table->integer('ChatUserAttachNum');

            $table->integer('UserNum');

            $table->integer('ChatNum');

            $table->integer('IsRead');

            $table->date('DateTimeRemoved');

            $table->integer('IsMute');

        });

    }

    public function down()
    {
        Schema::dropIfExists('chatuserattach');
    }
};
