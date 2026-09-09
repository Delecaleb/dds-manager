<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('dispsupply', function (Blueprint $table) {

            $table->integer('DispSupplyNum');

            $table->integer('SupplyNum');

            $table->integer('ProvNum');

            $table->date('DateDispensed');

            $table->string('DispQuantity');

            $table->text('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('dispsupply');
    }
};
