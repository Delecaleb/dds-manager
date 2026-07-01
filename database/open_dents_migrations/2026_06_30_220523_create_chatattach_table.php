<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('chatattach', function(Blueprint $table){

$table->integer('ChatAttachNum');

$table->integer('ChatMsgNum');

$table->string('FileName');

$table->string('Thumbnail');

$table->string('FileData');



});

}


public function down()
{
Schema::dropIfExists('chatattach');
}

};
