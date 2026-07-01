<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('pearlrequest', function(Blueprint $table){

$table->integer('PearlRequestNum');

$table->string('RequestId');

$table->integer('DocNum');

$table->integer('RequestStatus');

$table->date('DateTSent');

$table->date('DateTChecked');



});

}


public function down()
{
Schema::dropIfExists('pearlrequest');
}

};
