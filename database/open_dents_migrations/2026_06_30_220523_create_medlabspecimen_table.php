<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('medlabspecimen', function(Blueprint $table){

$table->integer('MedLabSpecimenNum');

$table->integer('MedLabNum');

$table->string('SpecimenID');

$table->string('SpecimenDescript');

$table->date('DateTimeCollected');



});

}


public function down()
{
Schema::dropIfExists('medlabspecimen');
}

};
