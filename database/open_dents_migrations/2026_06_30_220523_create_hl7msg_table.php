<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('hl7msg', function(Blueprint $table){

$table->integer('HL7MsgNum');

$table->integer('HL7Status');

$table->text('MsgText');

$table->integer('AptNum');

$table->string('DateTStamp');

$table->integer('PatNum');

$table->text('Note');



});

}


public function down()
{
Schema::dropIfExists('hl7msg');
}

};
