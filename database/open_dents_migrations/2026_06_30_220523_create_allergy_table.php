<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('allergy', function (Blueprint $table) {

            $table->integer('AllergyNum');

            $table->integer('AllergyDefNum');

            $table->integer('PatNum');

            $table->string('Reaction');

            $table->integer('StatusIsActive');

            $table->string('DateTStamp');

            $table->date('DateAdverseReaction');

            $table->string('SnomedReaction');

        });

    }

    public function down()
    {
        Schema::dropIfExists('allergy');
    }
};
