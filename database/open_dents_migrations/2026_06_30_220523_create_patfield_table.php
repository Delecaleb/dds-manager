<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patfield', function(Blueprint $table){

$table->integer('PatFieldNum');

$table->integer('PatNum');

$table->string('FieldName');

$table->text('FieldValue');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');



});

}


public function down()
{
Schema::dropIfExists('patfield');
}

};
