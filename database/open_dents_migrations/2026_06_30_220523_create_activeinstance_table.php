<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('activeinstance', function (Blueprint $table) {

            $table->integer('ActiveInstanceNum');

            $table->integer('ComputerNum');

            $table->integer('UserNum');

            $table->integer('ProcessId');

            $table->date('DateTimeLastActive');

            $table->date('DateTRecorded');

            $table->integer('ConnectionType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('activeinstance');
    }
};
