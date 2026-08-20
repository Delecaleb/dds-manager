<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('insbluebook', function (Blueprint $table) {

            $table->integer('InsBlueBookNum');

            $table->integer('ProcCodeNum');

            $table->integer('CarrierNum');

            $table->integer('PlanNum');

            $table->string('GroupNum');

            $table->string('InsPayAmt');

            $table->string('AllowedOverride');

            $table->date('DateTEntry');

            $table->integer('ProcNum');

            $table->date('ProcDate');

            $table->string('ClaimType');

            $table->integer('ClaimNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('insbluebook');
    }
};
