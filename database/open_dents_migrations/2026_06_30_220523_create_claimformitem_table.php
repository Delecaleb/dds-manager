<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimformitem', function(Blueprint $table){

$table->integer('ClaimFormItemNum');

$table->integer('ClaimFormNum');

$table->string('ImageFileName');

$table->string('FieldName');

$table->string('FormatString');

$table->string('XPos');

$table->string('YPos');

$table->string('Width');

$table->string('Height');



});

}


public function down()
{
Schema::dropIfExists('claimformitem');
}

};
