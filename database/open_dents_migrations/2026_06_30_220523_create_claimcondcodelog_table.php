<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('claimcondcodelog', function (Blueprint $table) {

            $table->integer('ClaimCondCodeLogNum');

            $table->integer('ClaimNum');

            $table->string('Code0');

            $table->string('Code1');

            $table->string('Code2');

            $table->string('Code3');

            $table->string('Code4');

            $table->string('Code5');

            $table->string('Code6');

            $table->string('Code7');

            $table->string('Code8');

            $table->string('Code9');

            $table->string('Code10');

        });

    }

    public function down()
    {
        Schema::dropIfExists('claimcondcodelog');
    }
};
