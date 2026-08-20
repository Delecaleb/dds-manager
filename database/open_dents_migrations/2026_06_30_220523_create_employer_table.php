<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('employer', function (Blueprint $table) {

            $table->integer('EmployerNum');

            $table->string('EmpName');

            $table->string('Address');

            $table->string('Address2');

            $table->string('City');

            $table->string('State');

            $table->string('Zip');

            $table->string('Phone');

        });

    }

    public function down()
    {
        Schema::dropIfExists('employer');
    }
};
