<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrquarterlykey', function (Blueprint $table) {

            $table->integer('EhrQuarterlyKeyNum');

            $table->integer('YearValue');

            $table->integer('QuarterValue');

            $table->string('PracticeName');

            $table->string('KeyValue');

            $table->integer('PatNum');

            $table->text('Notes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrquarterlykey');
    }
};
