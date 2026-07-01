<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('orthohardware', function(Blueprint $table){

$table->integer('OrthoHardwareNum');

$table->integer('PatNum');

$table->date('DateExam');

$table->integer('OrthoHardwareType');

$table->integer('OrthoHardwareSpecNum');

$table->string('ToothRange');

$table->string('Note');

$table->integer('IsHidden');



});

}


public function down()
{
Schema::dropIfExists('orthohardware');
}

};
