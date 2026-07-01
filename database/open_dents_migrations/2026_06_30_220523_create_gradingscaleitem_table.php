<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('gradingscaleitem', function(Blueprint $table){

$table->integer('GradingScaleItemNum');

$table->integer('GradingScaleNum');

$table->string('GradeShowing');

$table->string('GradeNumber');

$table->string('Description');



});

}


public function down()
{
Schema::dropIfExists('gradingscaleitem');
}

};
