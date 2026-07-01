<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlabnote', function(Blueprint $table){

$table->integer('EhrLabNoteNum');

$table->integer('EhrLabNum');

$table->integer('EhrLabResultNum');

$table->text('Comments');



});

}


public function down()
{
Schema::dropIfExists('ehrlabnote');
}

};
