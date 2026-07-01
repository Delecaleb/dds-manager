<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('clinicpref', function(Blueprint $table){

$table->integer('ClinicPrefNum');

$table->integer('ClinicNum');

$table->string('PrefName');

$table->text('ValueString');



});

}


public function down()
{
Schema::dropIfExists('clinicpref');
}

};
