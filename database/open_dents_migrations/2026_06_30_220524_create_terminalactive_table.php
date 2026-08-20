<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('terminalactive', function (Blueprint $table) {

            $table->integer('TerminalActiveNum');

            $table->string('ComputerName');

            $table->integer('TerminalStatus');

            $table->integer('PatNum');

            $table->integer('SessionId');

            $table->integer('ProcessId');

            $table->string('SessionName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('terminalactive');
    }
};
