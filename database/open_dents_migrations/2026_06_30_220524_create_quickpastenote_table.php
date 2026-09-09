<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('quickpastenote', function (Blueprint $table) {

            $table->integer('QuickPasteNoteNum');

            $table->integer('QuickPasteCatNum');

            $table->integer('ItemOrder');

            $table->text('Note');

            $table->string('Abbreviation');

        });

    }

    public function down()
    {
        Schema::dropIfExists('quickpastenote');
    }
};
