<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('emailmessageuid', function(Blueprint $table){

$table->integer('EmailMessageUidNum');

$table->text('MsgId');

$table->string('RecipientAddress');



});

}


public function down()
{
Schema::dropIfExists('emailmessageuid');
}

};
