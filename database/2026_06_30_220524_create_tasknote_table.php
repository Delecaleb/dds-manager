<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('tasknote', function(Blueprint $table){

$table->integer('TaskNoteNum');

$table->integer('TaskNum');

$table->integer('UserNum');

$table->date('DateTimeNote');

$table->text('Note');



});

}


public function down()
{
Schema::dropIfExists('tasknote');
}

};
