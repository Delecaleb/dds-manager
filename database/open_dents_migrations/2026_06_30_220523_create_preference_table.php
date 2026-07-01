<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('preference', function(Blueprint $table){

$table->string('PrefName');

$table->text('ValueString');

$table->integer('PrefNum');

$table->text('Comments');



});

}


public function down()
{
Schema::dropIfExists('preference');
}

};
