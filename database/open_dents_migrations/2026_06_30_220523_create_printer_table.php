<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('printer', function (Blueprint $table) {

            $table->integer('PrinterNum');

            $table->integer('ComputerNum');

            $table->integer('PrintSit');

            $table->string('PrinterName');

            $table->integer('DisplayPrompt');

            $table->string('FileExtension');

            $table->integer('IsVirtualPrinter');

        });

    }

    public function down()
    {
        Schema::dropIfExists('printer');
    }
};
