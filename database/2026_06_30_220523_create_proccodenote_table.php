<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('proccodenote', function(Blueprint $table){

$table->integer('ProcCodeNoteNum');

$table->integer('CodeNum');

$table->integer('ProvNum');

$table->text('Note');

$table->string('ProcTime');

$table->integer('ProcStatus');



});

}


public function down()
{
Schema::dropIfExists('proccodenote');
}

};
