<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('cloudaddress', function (Blueprint $table) {

            $table->integer('CloudAddressNum');

            $table->string('IpAddress');

            $table->integer('UserNumLastConnect');

            $table->date('DateTimeLastConnect');

        });

    }

    public function down()
    {
        Schema::dropIfExists('cloudaddress');
    }
};
