<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('documentmisc', function(Blueprint $table){

$table->integer('DocMiscNum');

$table->date('DateCreated');

$table->string('FileName');

$table->integer('DocMiscType');

$table->text('RawBase64');



});

}


public function down()
{
Schema::dropIfExists('documentmisc');
}

};
