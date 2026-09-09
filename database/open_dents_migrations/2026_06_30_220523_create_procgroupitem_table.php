<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('procgroupitem', function (Blueprint $table) {

            $table->integer('ProcGroupItemNum');

            $table->integer('ProcNum');

            $table->integer('GroupNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('procgroupitem');
    }
};
