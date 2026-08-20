<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('recall', function (Blueprint $table) {

            $table->integer('RecallNum');

            $table->integer('PatNum');

            $table->date('DateDueCalc');

            $table->date('DateDue');

            $table->date('DatePrevious');

            $table->integer('RecallInterval');

            $table->integer('RecallStatus');

            $table->text('Note');

            $table->integer('IsDisabled');

            $table->string('DateTStamp');

            $table->integer('RecallTypeNum');

            $table->string('DisableUntilBalance');

            $table->date('DisableUntilDate');

            $table->date('DateScheduled');

            $table->integer('Priority');

            $table->string('TimePatternOverride');

        });

    }

    public function down()
    {
        Schema::dropIfExists('recall');
    }
};
