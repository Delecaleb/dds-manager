<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('erouting', function(Blueprint $table){

$table->integer('ERoutingNum');

$table->string('Description');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->date('SecDateTEntry');

$table->integer('IsComplete');



});

}


public function down()
{
Schema::dropIfExists('erouting');
}

};
