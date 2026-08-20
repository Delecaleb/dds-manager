<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('certemployee', function (Blueprint $table) {

            $table->integer('CertEmployeeNum');

            $table->integer('CertNum');

            $table->integer('EmployeeNum');

            $table->date('DateCompleted');

            $table->string('Note');

            $table->integer('UserNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('certemployee');
    }
};
