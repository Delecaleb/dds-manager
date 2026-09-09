<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('requiredfieldcondition', function (Blueprint $table) {

            $table->integer('RequiredFieldConditionNum');

            $table->integer('RequiredFieldNum');

            $table->string('ConditionType');

            $table->integer('Operator');

            $table->string('ConditionValue');

            $table->integer('ConditionRelationship');

        });

    }

    public function down()
    {
        Schema::dropIfExists('requiredfieldcondition');
    }
};
