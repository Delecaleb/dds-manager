<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('taskhist', function (Blueprint $table) {

            $table->integer('TaskHistNum');

            $table->integer('UserNumHist');

            $table->date('DateTStamp');

            $table->integer('IsNoteChange');

            $table->integer('TaskNum');

            $table->integer('TaskListNum');

            $table->date('DateTask');

            $table->integer('KeyNum');

            $table->text('Descript');

            $table->integer('TaskStatus');

            $table->integer('IsRepeating');

            $table->integer('DateType');

            $table->integer('FromNum');

            $table->integer('ObjectType');

            $table->date('DateTimeEntry');

            $table->integer('UserNum');

            $table->date('DateTimeFinished');

            $table->integer('PriorityDefNum');

            $table->string('ReminderGroupId');

            $table->integer('ReminderType');

            $table->integer('ReminderFrequency');

            $table->date('DateTimeOriginal');

            $table->string('SecDateTEdit');

            $table->string('DescriptOverride');

            $table->integer('IsReadOnly');

            $table->integer('Category');

            $table->integer('TriagePosition');

        });

    }

    public function down()
    {
        Schema::dropIfExists('taskhist');
    }
};
