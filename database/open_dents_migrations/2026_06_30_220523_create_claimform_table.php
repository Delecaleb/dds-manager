<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimform', function(Blueprint $table){

$table->integer('ClaimFormNum');

$table->string('Description');

$table->integer('IsHidden');

$table->string('FontName');

$table->string('FontSize');

$table->string('UniqueID');

$table->integer('PrintImages');

$table->integer('OffsetX');

$table->integer('OffsetY');

$table->integer('Width');

$table->integer('Height');



});

}


public function down()
{
Schema::dropIfExists('claimform');
}

};
