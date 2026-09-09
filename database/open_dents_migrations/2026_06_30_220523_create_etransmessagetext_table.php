<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('etransmessagetext', function (Blueprint $table) {

            $table->integer('EtransMessageTextNum');

            $table->text('MessageText');

        });

    }

    public function down()
    {
        Schema::dropIfExists('etransmessagetext');
    }
};
