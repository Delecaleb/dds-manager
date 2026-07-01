<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('codegroup', function(Blueprint $table){

$table->integer('CodeGroupNum');

$table->string('GroupName');

$table->text('ProcCodes');

$table->integer('ItemOrder');

$table->integer('CodeGroupFixed');

$table->integer('IsHidden');

$table->integer('ShowInAgeLimit');

$table->integer('ShowInFrequency');

$table->integer('ShowInOther');

$table->integer('ShowInHistory');

$table->string('HistProcCode');

$table->integer('IsPerioFourQuads');



});

}


public function down()
{
Schema::dropIfExists('codegroup');
}

};
