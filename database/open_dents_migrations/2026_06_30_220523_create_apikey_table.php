<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('apikey', function(Blueprint $table){

$table->integer('APIKeyNum');

$table->string('CustApiKey');

$table->string('DevName');



});

}


public function down()
{
Schema::dropIfExists('apikey');
}

};
