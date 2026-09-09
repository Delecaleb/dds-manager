<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eobattach', function (Blueprint $table) {

            $table->integer('EobAttachNum');

            $table->integer('ClaimPaymentNum');

            $table->date('DateTCreated');

            $table->string('FileName');

            $table->text('RawBase64');

            $table->integer('ClaimNumPreAuth');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eobattach');
    }
};
