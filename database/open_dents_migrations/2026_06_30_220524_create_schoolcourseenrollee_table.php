<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('schoolcourseenrollee', function(Blueprint $table){

$table->integer('SchoolCourseEnrolleeNum');

$table->integer('SchoolCourseNum');

$table->integer('StudentNum');

$table->string('GradeNumber');

$table->string('GradeOverride');



});

}


public function down()
{
Schema::dropIfExists('schoolcourseenrollee');
}

};
