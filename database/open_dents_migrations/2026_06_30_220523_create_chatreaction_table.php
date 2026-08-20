<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('chatreaction', function (Blueprint $table) {

            $table->integer('ChatReactionNum');

            $table->integer('ChatMsgNum');

            $table->integer('UserNum');

            $table->string('EmojiName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('chatreaction');
    }
};
