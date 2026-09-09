<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('fhircontactpoint', function (Blueprint $table) {

            $table->integer('FHIRContactPointNum');

            $table->integer('FHIRSubscriptionNum');

            $table->integer('ContactSystem');

            $table->string('ContactValue');

            $table->integer('ContactUse');

            $table->integer('ItemOrder');

            $table->date('DateStart');

            $table->date('DateEnd');

        });

    }

    public function down()
    {
        Schema::dropIfExists('fhircontactpoint');
    }
};
