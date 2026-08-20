<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('custreference', function (Blueprint $table) {

            $table->integer('CustReferenceNum');

            $table->integer('PatNum');

            $table->date('DateMostRecent');

            $table->string('Note');

            $table->integer('IsBadRef');

        });

    }

    public function down()
    {
        Schema::dropIfExists('custreference');
    }
};
