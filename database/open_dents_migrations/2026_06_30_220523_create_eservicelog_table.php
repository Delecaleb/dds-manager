<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eservicelog', function (Blueprint $table) {

            $table->integer('EServiceLogNum');

            $table->date('LogDateTime');

            $table->integer('PatNum');

            $table->integer('EServiceType');

            $table->integer('EServiceAction');

            $table->integer('KeyType');

            $table->string('LogGuid');

            $table->integer('ClinicNum');

            $table->integer('FKey');

            $table->date('DateTimeUploaded');

            $table->string('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eservicelog');
    }
};
