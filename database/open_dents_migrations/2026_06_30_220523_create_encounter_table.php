<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('encounter', function(Blueprint $table){

$table->integer('EncounterNum');

$table->integer('PatNum');

$table->integer('ProvNum');

$table->string('CodeValue');

$table->string('CodeSystem');

$table->text('Note');

$table->date('DateEncounter');



});

}


public function down()
{
Schema::dropIfExists('encounter');
}

};
