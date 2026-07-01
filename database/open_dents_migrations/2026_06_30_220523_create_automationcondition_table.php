<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('automationcondition', function(Blueprint $table){

$table->integer('AutomationConditionNum');

$table->integer('AutomationNum');

$table->integer('CompareField');

$table->integer('Comparison');

$table->string('CompareString');



});

}


public function down()
{
Schema::dropIfExists('automationcondition');
}

};
