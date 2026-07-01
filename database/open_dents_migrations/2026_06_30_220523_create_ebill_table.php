<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ebill', function(Blueprint $table){

$table->integer('EbillNum');

$table->integer('ClinicNum');

$table->string('ClientAcctNumber');

$table->string('ElectUserName');

$table->string('ElectPassword');

$table->integer('PracticeAddress');

$table->integer('RemitAddress');



});

}


public function down()
{
Schema::dropIfExists('ebill');
}

};
