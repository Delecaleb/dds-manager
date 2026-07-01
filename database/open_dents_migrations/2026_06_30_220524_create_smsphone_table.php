<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('smsphone', function(Blueprint $table){

$table->integer('SmsPhoneNum');

$table->integer('ClinicNum');

$table->string('PhoneNumber');

$table->date('DateTimeActive');

$table->date('DateTimeInactive');

$table->string('InactiveCode');

$table->string('CountryCode');



});

}


public function down()
{
Schema::dropIfExists('smsphone');
}

};
