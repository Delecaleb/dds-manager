<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('chatuserod', function(Blueprint $table){

$table->integer('ChatUserodNum');

$table->integer('UserNum');

$table->integer('UserStatus');

$table->date('DateTimeStatusReset');

$table->text('Photo');

$table->string('PhotoCrop');

$table->integer('OpenBackground');

$table->integer('CloseKeepRunning');

$table->integer('MuteNotifications');

$table->integer('DismissNotifySecs');

$table->integer('MuteImportantNotifications');

$table->integer('DismissImportantNotifySecs');

$table->integer('PlayNotificationSound');



});

}


public function down()
{
Schema::dropIfExists('chatuserod');
}

};
