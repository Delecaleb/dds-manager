<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('loginattempt', function(Blueprint $table){

$table->integer('LoginAttemptNum');

$table->string('UserName');

$table->integer('LoginType');

$table->date('DateTFail');



});

}


public function down()
{
Schema::dropIfExists('loginattempt');
}

};
