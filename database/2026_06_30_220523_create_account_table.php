<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    public function up()
    {

        Schema::create('account', function (Blueprint $table) {

            $table->integer('AccountNum');

            $table->string('Description');

            $table->integer('AcctType');

            $table->string('BankNumber');

            $table->integer('Inactive');

            $table->integer('AccountColor');

            $table->integer('IsRetainedEarnings');



        });

    }


    public function down()
    {
        Schema::dropIfExists('account');
    }

};
