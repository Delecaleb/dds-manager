<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('fielddeflink', function(Blueprint $table){

$table->integer('FieldDefLinkNum');

$table->integer('FieldDefNum');

$table->integer('FieldDefType');

$table->integer('FieldLocation');



});

}


public function down()
{
Schema::dropIfExists('fielddeflink');
}

};
