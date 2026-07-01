<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('insplanpreference', function(Blueprint $table){

$table->integer('InsPlanPrefNum');

$table->integer('PlanNum');

$table->integer('FKey');

$table->integer('FKeyType');

$table->text('ValueString');



});

}


public function down()
{
Schema::dropIfExists('insplanpreference');
}

};
