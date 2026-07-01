<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('feeschedgroup', function(Blueprint $table){

$table->integer('FeeSchedGroupNum');

$table->string('Description');

$table->integer('FeeSchedNum');

$table->string('ClinicNums');



});

}


public function down()
{
Schema::dropIfExists('feeschedgroup');
}

};
