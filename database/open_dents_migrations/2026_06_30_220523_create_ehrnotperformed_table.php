<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrnotperformed', function(Blueprint $table){

$table->integer('EhrNotPerformedNum');

$table->integer('PatNum');

$table->integer('ProvNum');

$table->string('CodeValue');

$table->string('CodeSystem');

$table->string('CodeValueReason');

$table->string('CodeSystemReason');

$table->text('Note');

$table->date('DateEntry');



});

}


public function down()
{
Schema::dropIfExists('ehrnotperformed');
}

};
