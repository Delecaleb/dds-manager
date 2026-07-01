<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('program', function(Blueprint $table){

$table->integer('ProgramNum');

$table->string('ProgName');

$table->string('ProgDesc');

$table->integer('Enabled');

$table->text('Path');

$table->text('CommandLine');

$table->text('Note');

$table->string('PluginDllName');

$table->text('ButtonImage');

$table->text('FileTemplate');

$table->string('FilePath');

$table->integer('IsDisabledByHq');

$table->string('CustErr');



});

}


public function down()
{
Schema::dropIfExists('program');
}

};
