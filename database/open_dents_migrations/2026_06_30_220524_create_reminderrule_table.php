<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('reminderrule', function(Blueprint $table){

$table->integer('ReminderRuleNum');

$table->integer('ReminderCriterion');

$table->integer('CriterionFK');

$table->string('CriterionValue');

$table->string('Message');



});

}


public function down()
{
Schema::dropIfExists('reminderrule');
}

};
