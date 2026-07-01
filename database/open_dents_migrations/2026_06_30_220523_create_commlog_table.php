<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('commlog', function(Blueprint $table){

$table->integer('CommlogNum');

$table->integer('PatNum');

$table->date('CommDateTime');

$table->integer('CommType');

$table->text('Note');

$table->integer('Mode_');

$table->integer('SentOrReceived');

$table->integer('UserNum');

$table->text('Signature');

$table->integer('SigIsTopaz');

$table->string('DateTStamp');

$table->date('DateTimeEnd');

$table->integer('CommSource');

$table->integer('ProgramNum');

$table->date('DateTEntry');

$table->integer('ReferralNum');

$table->integer('CommReferralBehavior');



});

}


public function down()
{
Schema::dropIfExists('commlog');
}

};
