<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('familyhealth', function (Blueprint $table) {

            $table->integer('FamilyHealthNum');

            $table->integer('PatNum');

            $table->integer('Relationship');

            $table->integer('DiseaseDefNum');

            $table->string('PersonName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('familyhealth');
    }
};
