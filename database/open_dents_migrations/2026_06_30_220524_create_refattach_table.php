<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('refattach', function (Blueprint $table) {

            $table->integer('RefAttachNum');

            $table->integer('ReferralNum');

            $table->integer('PatNum');

            $table->integer('ItemOrder');

            $table->date('RefDate');

            $table->integer('RefType');

            $table->integer('RefToStatus');

            $table->text('Note');

            $table->integer('IsTransitionOfCare');

            $table->integer('ProcNum');

            $table->date('DateProcComplete');

            $table->integer('ProvNum');

            $table->string('DateTStamp');

        });

    }

    public function down()
    {
        Schema::dropIfExists('refattach');
    }
};
