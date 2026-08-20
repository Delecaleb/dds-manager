<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('medicalorder', function (Blueprint $table) {

            $table->integer('MedicalOrderNum');

            $table->integer('MedOrderType');

            $table->integer('PatNum');

            $table->date('DateTimeOrder');

            $table->string('Description');

            $table->integer('IsDiscontinued');

            $table->integer('ProvNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('medicalorder');
    }
};
