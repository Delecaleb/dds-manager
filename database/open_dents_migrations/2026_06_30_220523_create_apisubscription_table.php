<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('apisubscription', function (Blueprint $table) {

            $table->integer('ApiSubscriptionNum');

            $table->string('EndPointUrl');

            $table->string('Workstation');

            $table->string('CustomerKey');

            $table->string('WatchTable');

            $table->integer('PollingSeconds');

            $table->string('UiEventType');

            $table->date('DateTimeStart');

            $table->date('DateTimeStop');

            $table->string('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('apisubscription');
    }
};
