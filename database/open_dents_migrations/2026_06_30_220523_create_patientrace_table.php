<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patientrace', function(Blueprint $table){

$table->integer('PatientRaceNum');

$table->integer('PatNum');

$table->integer('Race');

$table->string('CdcrecCode');



});

}


public function down()
{
Schema::dropIfExists('patientrace');
}

};
