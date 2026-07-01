<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('inseditlog', function(Blueprint $table){

$table->integer('InsEditLogNum');

$table->integer('FKey');

$table->integer('LogType');

$table->string('FieldName');

$table->string('OldValue');

$table->string('NewValue');

$table->integer('UserNum');

$table->string('DateTStamp');

$table->integer('ParentKey');

$table->string('Description');

$table->text('OldValueBig');

$table->text('NewValueBig');



});

}


public function down()
{
Schema::dropIfExists('inseditlog');
}

};
