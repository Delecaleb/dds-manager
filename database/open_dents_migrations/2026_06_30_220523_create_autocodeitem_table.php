<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('autocodeitem', function(Blueprint $table){

$table->integer('AutoCodeItemNum');

$table->integer('AutoCodeNum');

$table->string('OldCode');

$table->integer('CodeNum');



});

}


public function down()
{
Schema::dropIfExists('autocodeitem');
}

};
