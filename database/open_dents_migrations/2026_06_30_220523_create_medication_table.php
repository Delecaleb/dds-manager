<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('medication', function(Blueprint $table){

$table->integer('MedicationNum');

$table->string('MedName');

$table->integer('GenericNum');

$table->text('Notes');

$table->string('DateTStamp');

$table->integer('RxCui');

$table->integer('IsHidden');



});

}


public function down()
{
Schema::dropIfExists('medication');
}

};
