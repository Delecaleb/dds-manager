<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('apptviewitem', function(Blueprint $table){

$table->integer('ApptViewItemNum');

$table->integer('ApptViewNum');

$table->integer('OpNum');

$table->integer('ProvNum');

$table->string('ElementDesc');

$table->integer('ElementOrder');

$table->integer('ElementColor');

$table->integer('ElementAlignment');

$table->integer('ApptFieldDefNum');

$table->integer('PatFieldDefNum');

$table->integer('IsMobile');



});

}


public function down()
{
Schema::dropIfExists('apptviewitem');
}

};
