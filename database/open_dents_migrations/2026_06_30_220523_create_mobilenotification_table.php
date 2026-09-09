<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('mobilenotification', function (Blueprint $table) {

            $table->integer('MobileNotificationNum');

            $table->integer('NotificationType');

            $table->string('DeviceId');

            $table->text('PrimaryKeys');

            $table->text('Tags');

            $table->date('DateTimeEntry');

            $table->date('DateTimeExpires');

            $table->integer('AppTarget');

        });

    }

    public function down()
    {
        Schema::dropIfExists('mobilenotification');
    }
};
