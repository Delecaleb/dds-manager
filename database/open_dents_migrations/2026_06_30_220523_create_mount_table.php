<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mount', function (Blueprint $table) {

            $table->integer('MountNum');

            $table->integer('PatNum');

            $table->integer('DocCategory');

            $table->date('DateCreated');

            $table->string('Description');

            $table->text('Note');

            $table->integer('Width');

            $table->integer('Height');

            $table->integer('ColorBack');

            $table->integer('ProvNum');

            $table->integer('ColorFore');

            $table->integer('ColorTextBack');

            $table->integer('FlipOnAcquire');

            $table->integer('AdjModeAfterSeries');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mount');
    }
};
