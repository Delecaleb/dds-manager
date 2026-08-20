<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('entrylog', function (Blueprint $table) {

            $table->integer('EntryLogNum');

            $table->integer('UserNum');

            $table->integer('FKeyType');

            $table->integer('FKey');

            $table->integer('LogSource');

            $table->date('EntryDateTime');

        });

    }

    public function down()
    {
        Schema::dropIfExists('entrylog');
    }
};
