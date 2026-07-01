<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('allergydef', function(Blueprint $table){

$table->integer('AllergyDefNum');

$table->string('Description');

$table->integer('IsHidden');

$table->string('DateTStamp');

$table->integer('SnomedType');

$table->integer('MedicationNum');

$table->string('UniiCode');



});

}


public function down()
{
Schema::dropIfExists('allergydef');
}

};
