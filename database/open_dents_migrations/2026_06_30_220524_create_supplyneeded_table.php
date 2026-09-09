<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('supplyneeded', function (Blueprint $table) {

            $table->integer('SupplyNeededNum');

            $table->text('Description');

            $table->date('DateAdded');

        });

    }

    public function down()
    {
        Schema::dropIfExists('supplyneeded');
    }
};
