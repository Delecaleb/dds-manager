<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('procnote', function (Blueprint $table) {

            $table->integer('ProcNoteNum');

            $table->integer('PatNum');

            $table->integer('ProcNum');

            $table->date('EntryDateTime');

            $table->integer('UserNum');

            $table->text('Note');

            $table->integer('SigIsTopaz');

            $table->text('Signature');

            $table->integer('UserNum2');

            $table->text('Signature2');

            $table->integer('SigIsTopaz2');

        });

    }

    public function down()
    {
        Schema::dropIfExists('procnote');
    }
};
