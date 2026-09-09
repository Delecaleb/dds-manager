<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('wikipage', function (Blueprint $table) {

            $table->integer('WikiPageNum');

            $table->integer('UserNum');

            $table->string('PageTitle');

            $table->string('KeyWords');

            $table->text('PageContent');

            $table->date('DateTimeSaved');

            $table->integer('IsDraft');

            $table->integer('IsLocked');

            $table->integer('IsDeleted');

            $table->text('PageContentPlainText');

        });

    }

    public function down()
    {
        Schema::dropIfExists('wikipage');
    }
};
