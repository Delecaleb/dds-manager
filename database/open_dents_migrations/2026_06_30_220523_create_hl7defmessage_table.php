<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('hl7defmessage', function (Blueprint $table) {

            $table->integer('HL7DefMessageNum');

            $table->integer('HL7DefNum');

            $table->string('MessageType');

            $table->string('EventType');

            $table->integer('InOrOut');

            $table->integer('ItemOrder');

            $table->text('Note');

            $table->string('MessageStructure');

        });

    }

    public function down()
    {
        Schema::dropIfExists('hl7defmessage');
    }
};
