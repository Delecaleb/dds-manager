<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('deletedobject', function(Blueprint $table){

$table->integer('DeletedObjectNum');

$table->integer('ObjectNum');

$table->integer('ObjectType');

$table->string('DateTStamp');



});

}


public function down()
{
Schema::dropIfExists('deletedobject');
}

};
