<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('dashboardlayout', function (Blueprint $table) {

            $table->integer('DashboardLayoutNum');

            $table->integer('UserNum');

            $table->integer('UserGroupNum');

            $table->string('DashboardTabName');

            $table->integer('DashboardTabOrder');

            $table->integer('DashboardRows');

            $table->integer('DashboardColumns');

            $table->string('DashboardGroupName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('dashboardlayout');
    }
};
