<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('fhirsubscription', function (Blueprint $table) {

            $table->integer('FHIRSubscriptionNum');

            $table->string('Criteria');

            $table->string('Reason');

            $table->integer('SubStatus');

            $table->text('ErrorNote');

            $table->integer('ChannelType');

            $table->string('ChannelEndpoint');

            $table->string('ChannelPayLoad');

            $table->string('ChannelHeader');

            $table->date('DateEnd');

            $table->string('APIKeyHash');

        });

    }

    public function down()
    {
        Schema::dropIfExists('fhirsubscription');
    }
};
