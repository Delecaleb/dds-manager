<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('tasksubscription', function(Blueprint $table){

$table->integer('TaskSubscriptionNum');

$table->integer('UserNum');

$table->integer('TaskListNum');

$table->integer('TaskNum');



});

}


public function down()
{
Schema::dropIfExists('tasksubscription');
}

};
