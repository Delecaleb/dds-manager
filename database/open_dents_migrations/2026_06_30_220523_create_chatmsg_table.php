<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('chatmsg', function(Blueprint $table){

$table->integer('ChatMsgNum');

$table->integer('ChatNum');

$table->integer('UserNum');

$table->date('DateTimeSent');

$table->text('Message');

$table->integer('SeqCount');

$table->integer('Quote');

$table->integer('EventType');

$table->integer('IsImportant');



});

}


public function down()
{
Schema::dropIfExists('chatmsg');
}

};
