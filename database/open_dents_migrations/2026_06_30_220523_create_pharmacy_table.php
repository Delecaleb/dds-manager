<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('pharmacy', function (Blueprint $table) {

            $table->integer('PharmacyNum');

            $table->string('PharmID');

            $table->string('StoreName');

            $table->string('Phone');

            $table->string('Fax');

            $table->string('Address');

            $table->string('Address2');

            $table->string('City');

            $table->string('State');

            $table->string('Zip');

            $table->text('Note');

            $table->string('DateTStamp');

        });

    }

    public function down()
    {
        Schema::dropIfExists('pharmacy');
    }
};
