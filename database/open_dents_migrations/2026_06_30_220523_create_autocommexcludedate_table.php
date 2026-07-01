<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('autocommexcludedate', function(Blueprint $table){

$table->integer('AutoCommExcludeDateNum');

$table->integer('ClinicNum');

$table->date('DateExclude');



});

}


public function down()
{
Schema::dropIfExists('autocommexcludedate');
}

};
