<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrsummaryccd', function (Blueprint $table) {

            $table->integer('EhrSummaryCcdNum');

            $table->integer('PatNum');

            $table->date('DateSummary');

            $table->text('ContentSummary');

            $table->integer('EmailAttachNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrsummaryccd');
    }
};
