<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('insbluebookrule', function (Blueprint $table) {

            $table->integer('InsBlueBookRuleNum');

            $table->integer('ItemOrder');

            $table->integer('RuleType');

            $table->integer('LimitValue');

            $table->integer('LimitType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('insbluebookrule');
    }
};
