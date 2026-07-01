<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('apptfield', function(Blueprint $table){

$table->integer('ApptFieldNum');

$table->integer('AptNum');

$table->string('FieldName');

$table->text('FieldValue');



});

}


public function down()
{
Schema::dropIfExists('apptfield');
}

};
