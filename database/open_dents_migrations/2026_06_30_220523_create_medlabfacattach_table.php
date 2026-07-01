<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('medlabfacattach', function(Blueprint $table){

$table->integer('MedLabFacAttachNum');

$table->integer('MedLabNum');

$table->integer('MedLabResultNum');

$table->integer('MedLabFacilityNum');



});

}


public function down()
{
Schema::dropIfExists('medlabfacattach');
}

};
