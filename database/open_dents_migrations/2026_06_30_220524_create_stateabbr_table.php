<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('stateabbr', function(Blueprint $table){

$table->integer('StateAbbrNum');

$table->string('Description');

$table->string('Abbr');

$table->integer('MedicaidIDLength');



});

}


public function down()
{
Schema::dropIfExists('stateabbr');
}

};
