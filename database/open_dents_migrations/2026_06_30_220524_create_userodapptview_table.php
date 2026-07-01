<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('userodapptview', function(Blueprint $table){

$table->integer('UserodApptViewNum');

$table->integer('UserNum');

$table->integer('ClinicNum');

$table->integer('ApptViewNum');



});

}


public function down()
{
Schema::dropIfExists('userodapptview');
}

};
