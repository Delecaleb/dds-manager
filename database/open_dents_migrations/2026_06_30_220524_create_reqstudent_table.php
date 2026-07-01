<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('reqstudent', function(Blueprint $table){

$table->integer('ReqStudentNum');

$table->integer('ReqNeededNum');

$table->string('Descript');

$table->integer('SchoolCourseNum');

$table->integer('ProvNum');

$table->integer('AptNum');

$table->integer('PatNum');

$table->integer('InstructorNum');

$table->date('DateCompleted');

$table->integer('ProcNum');



});

}


public function down()
{
Schema::dropIfExists('reqstudent');
}

};
