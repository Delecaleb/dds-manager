<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('reconcile', function(Blueprint $table){

$table->integer('ReconcileNum');

$table->integer('AccountNum');

$table->string('StartingBal');

$table->string('EndingBal');

$table->date('DateReconcile');

$table->integer('IsLocked');



});

}


public function down()
{
Schema::dropIfExists('reconcile');
}

};
