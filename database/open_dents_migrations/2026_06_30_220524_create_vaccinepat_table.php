<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('vaccinepat', function (Blueprint $table) {

            $table->integer('VaccinePatNum');

            $table->integer('VaccineDefNum');

            $table->date('DateTimeStart');

            $table->date('DateTimeEnd');

            $table->string('AdministeredAmt');

            $table->integer('DrugUnitNum');

            $table->string('LotNumber');

            $table->integer('PatNum');

            $table->text('Note');

            $table->string('FilledCity');

            $table->string('FilledST');

            $table->integer('CompletionStatus');

            $table->integer('AdministrationNoteCode');

            $table->integer('UserNum');

            $table->integer('ProvNumOrdering');

            $table->integer('ProvNumAdminister');

            $table->date('DateExpire');

            $table->integer('RefusalReason');

            $table->integer('ActionCode');

            $table->integer('AdministrationRoute');

            $table->integer('AdministrationSite');

        });

    }

    public function down()
    {
        Schema::dropIfExists('vaccinepat');
    }
};
