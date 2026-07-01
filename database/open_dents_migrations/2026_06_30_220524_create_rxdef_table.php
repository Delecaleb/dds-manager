<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('rxdef', function(Blueprint $table){

$table->integer('RxDefNum');

$table->string('Drug');

$table->string('Sig');

$table->string('Disp');

$table->string('Refills');

$table->string('Notes');

$table->integer('IsControlled');

$table->integer('RxCui');

$table->integer('IsProcRequired');

$table->text('PatientInstruction');



});

}


public function down()
{
Schema::dropIfExists('rxdef');
}

};
