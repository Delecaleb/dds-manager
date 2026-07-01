<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('erxlog', function(Blueprint $table){

$table->integer('ErxLogNum');

$table->integer('PatNum');

$table->text('MsgText');

$table->string('DateTStamp');

$table->integer('ProvNum');

$table->integer('UserNum');



});

}


public function down()
{
Schema::dropIfExists('erxlog');
}

};
