<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eroutingaction', function (Blueprint $table) {

            $table->integer('ERoutingActionNum');

            $table->integer('ERoutingNum');

            $table->integer('ItemOrder');

            $table->integer('ERoutingActionType');

            $table->integer('UserNum');

            $table->integer('IsComplete');

            $table->date('DateTimeComplete');

            $table->integer('ForeignKeyType');

            $table->integer('ForeignKey');

            $table->string('LabelOverride');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eroutingaction');
    }
};
