<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('eformdef', function(Blueprint $table){

$table->integer('EFormDefNum');

$table->integer('FormType');

$table->string('Description');

$table->date('DateTCreated');

$table->integer('IsInternalHidden');

$table->integer('MaxWidth');

$table->integer('RevID');

$table->integer('ShowLabelsBold');

$table->integer('SpaceBelowEachField');

$table->integer('SpaceToRightEachField');

$table->integer('SaveImageCategory');



});

}


public function down()
{
Schema::dropIfExists('eformdef');
}

};
