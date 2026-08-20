<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('etrans835attach', function (Blueprint $table) {

            $table->integer('Etrans835AttachNum');

            $table->integer('EtransNum');

            $table->integer('ClaimNum');

            $table->integer('ClpSegmentIndex');

            $table->date('DateTimeEntry');

        });

    }

    public function down()
    {
        Schema::dropIfExists('etrans835attach');
    }
};
