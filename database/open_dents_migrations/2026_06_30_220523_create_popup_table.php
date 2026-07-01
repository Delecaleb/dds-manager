<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('popup', function(Blueprint $table){

$table->integer('PopupNum');

$table->integer('PatNum');

$table->text('Description');

$table->integer('IsDisabled');

$table->integer('PopupLevel');

$table->integer('UserNum');

$table->date('DateTimeEntry');

$table->integer('IsArchived');

$table->integer('PopupNumArchive');

$table->date('DateTimeDisabled');



});

}


public function down()
{
Schema::dropIfExists('popup');
}

};
