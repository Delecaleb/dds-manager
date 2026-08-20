<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('wikipagehist', function (Blueprint $table) {

            $table->integer('WikiPageNum');

            $table->integer('UserNum');

            $table->string('PageTitle');

            $table->text('PageContent');

            $table->date('DateTimeSaved');

            $table->integer('IsDeleted');

        });

    }

    public function down()
    {
        Schema::dropIfExists('wikipagehist');
    }
};
