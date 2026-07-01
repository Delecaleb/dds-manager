<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('insbluebooklog', function(Blueprint $table){

$table->integer('InsBlueBookLogNum');

$table->integer('ClaimProcNum');

$table->string('AllowedFee');

$table->date('DateTEntry');

$table->text('Description');



});

}


public function down()
{
Schema::dropIfExists('insbluebooklog');
}

};
