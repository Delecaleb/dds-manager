<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('perioexam', function(Blueprint $table){

$table->integer('PerioExamNum');

$table->integer('PatNum');

$table->date('ExamDate');

$table->integer('ProvNum');

$table->date('DateTMeasureEdit');

$table->text('Note');



});

}


public function down()
{
Schema::dropIfExists('perioexam');
}

};
