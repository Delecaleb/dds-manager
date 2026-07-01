<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('letter', function(Blueprint $table){

$table->integer('LetterNum');

$table->string('Description');

$table->text('BodyText');



});

}


public function down()
{
Schema::dropIfExists('letter');
}

};
