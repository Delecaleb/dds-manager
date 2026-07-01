<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('alertitem', function(Blueprint $table){

$table->integer('AlertItemNum');

$table->integer('ClinicNum');

$table->string('Description');

$table->integer('Type');

$table->integer('Severity');

$table->integer('Actions');

$table->integer('FormToOpen');

$table->integer('FKey');

$table->string('ItemValue');

$table->integer('UserNum');

$table->date('SecDateTEntry');



});

}


public function down()
{
Schema::dropIfExists('alertitem');
}

};
