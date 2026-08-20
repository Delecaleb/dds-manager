<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('centralconnection', function (Blueprint $table) {

            $table->integer('CentralConnectionNum');

            $table->string('ServerName');

            $table->string('DatabaseName');

            $table->string('MySqlUser');

            $table->string('MySqlPassword');

            $table->string('ServiceURI');

            $table->string('OdUser');

            $table->string('OdPassword');

            $table->text('Note');

            $table->integer('ItemOrder');

            $table->integer('WebServiceIsEcw');

            $table->string('ConnectionStatus');

            $table->integer('HasClinicBreakdownReports');

        });

    }

    public function down()
    {
        Schema::dropIfExists('centralconnection');
    }
};
