<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('cpt', function(Blueprint $table){

$table->integer('CptNum');

$table->string('CptCode');

$table->string('Description');

$table->string('VersionIDs');



});

}


public function down()
{
Schema::dropIfExists('cpt');
}

};
