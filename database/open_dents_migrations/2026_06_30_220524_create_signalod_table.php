<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('signalod', function(Blueprint $table){

$table->integer('SignalNum');

$table->date('DateViewing');

$table->date('SigDateTime');

$table->integer('FKey');

$table->string('FKeyType');

$table->integer('IType');

$table->integer('RemoteRole');

$table->text('MsgValue');



});

}


public function down()
{
Schema::dropIfExists('signalod');
}

};
