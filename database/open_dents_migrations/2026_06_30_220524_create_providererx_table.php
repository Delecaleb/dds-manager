<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('providererx', function (Blueprint $table) {

            $table->integer('ProviderErxNum');

            $table->integer('PatNum');

            $table->string('NationalProviderID');

            $table->integer('IsEnabled');

            $table->integer('IsIdentifyProofed');

            $table->integer('IsSentToHq');

            $table->integer('IsEpcs');

            $table->integer('ErxType');

            $table->string('UserId');

            $table->string('AccountId');

            $table->integer('RegistrationKeyNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('providererx');
    }
};
