<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('smsblockphone', function (Blueprint $table) {

            $table->integer('SmsBlockPhoneNum');

            $table->string('BlockWirelessNumber');

        });

    }

    public function down()
    {
        Schema::dropIfExists('smsblockphone');
    }
};
