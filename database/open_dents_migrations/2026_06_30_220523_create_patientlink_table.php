<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patientlink', function(Blueprint $table){

$table->integer('PatientLinkNum');

$table->integer('PatNumFrom');

$table->integer('PatNumTo');

$table->integer('LinkType');

$table->date('DateTimeLink');



});

}


public function down()
{
Schema::dropIfExists('patientlink');
}

};
