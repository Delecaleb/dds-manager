<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('laboratory', function(Blueprint $table){

$table->integer('LaboratoryNum');

$table->string('Description');

$table->string('Phone');

$table->text('Notes');

$table->integer('Slip');

$table->string('Address');

$table->string('City');

$table->string('State');

$table->string('Zip');

$table->string('Email');

$table->string('WirelessPhone');

$table->integer('IsHidden');



});

}


public function down()
{
Schema::dropIfExists('laboratory');
}

};
