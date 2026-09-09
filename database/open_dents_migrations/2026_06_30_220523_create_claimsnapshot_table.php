<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('claimsnapshot', function (Blueprint $table) {

            $table->integer('ClaimSnapshotNum');

            $table->integer('ProcNum');

            $table->string('ClaimType');

            $table->string('Writeoff');

            $table->string('InsPayEst');

            $table->string('Fee');

            $table->date('DateTEntry');

            $table->integer('ClaimProcNum');

            $table->integer('SnapshotTrigger');

        });

    }

    public function down()
    {
        Schema::dropIfExists('claimsnapshot');
    }
};
