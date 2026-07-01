<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    public function up()
    {

        Schema::create('adjustment', function (Blueprint $table) {

            $table->integer('AdjNum');

            $table->date('AdjDate');

            $table->string('AdjAmt');

            $table->integer('PatNum');

            $table->integer('AdjType');

            $table->integer('ProvNum');

            $table->text('AdjNote');

            $table->date('ProcDate');

            $table->integer('ProcNum');

            $table->date('DateEntry');

            $table->integer('ClinicNum');

            $table->integer('StatementNum');

            $table->integer('SecUserNumEntry');

            $table->string('SecDateTEdit');

            $table->integer('TaxTransID');



        });

    }


    public function down()
    {
        Schema::dropIfExists('adjustment');
    }

};
