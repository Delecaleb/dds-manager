<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('language', function(Blueprint $table){

$table->integer('LanguageNum');

$table->text('EnglishComments');

$table->text('ClassType');

$table->text('English');

$table->integer('IsObsolete');



});

}


public function down()
{
Schema::dropIfExists('language');
}

};
