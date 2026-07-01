<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('taskancestor', function(Blueprint $table){

$table->integer('TaskAncestorNum');

$table->integer('TaskNum');

$table->integer('TaskListNum');



});

}


public function down()
{
Schema::dropIfExists('taskancestor');
}

};
