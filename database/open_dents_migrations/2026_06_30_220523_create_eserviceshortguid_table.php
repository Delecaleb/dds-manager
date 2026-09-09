<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eserviceshortguid', function (Blueprint $table) {

            $table->integer('EServiceShortGuidNum');

            $table->string('EServiceCode');

            $table->string('ShortGuid');

            $table->string('ShortURL');

            $table->integer('FKey');

            $table->string('FKeyType');

            $table->date('DateTimeExpiration');

            $table->date('DateTEntry');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eserviceshortguid');
    }
};
