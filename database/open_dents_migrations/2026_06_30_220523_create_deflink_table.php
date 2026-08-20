<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('deflink', function (Blueprint $table) {

            $table->integer('DefLinkNum');

            $table->integer('DefNum');

            $table->integer('FKey');

            $table->integer('LinkType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('deflink');
    }
};
