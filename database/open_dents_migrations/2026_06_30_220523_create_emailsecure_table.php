<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('emailsecure', function(Blueprint $table){

$table->integer('EmailSecureNum');

$table->integer('ClinicNum');

$table->integer('PatNum');

$table->integer('EmailMessageNum');

$table->integer('EmailChainFK');

$table->integer('EmailFK');

$table->date('DateTEntry');

$table->string('SecDateTEdit');



});

}


public function down()
{
Schema::dropIfExists('emailsecure');
}

};
