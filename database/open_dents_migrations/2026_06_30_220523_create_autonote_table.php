<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('autonote', function(Blueprint $table){

$table->integer('AutoNoteNum');

$table->string('AutoNoteName');

$table->text('MainText');

$table->integer('Category');



});

}


public function down()
{
Schema::dropIfExists('autonote');
}

};
