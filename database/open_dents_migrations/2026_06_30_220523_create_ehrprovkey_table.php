<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrprovkey', function (Blueprint $table) {

            $table->integer('EhrProvKeyNum');

            $table->integer('PatNum');

            $table->string('LName');

            $table->string('FName');

            $table->string('ProvKey');

            $table->string('FullTimeEquiv');

            $table->text('Notes');

            $table->integer('YearValue');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrprovkey');
    }
};
