<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('queryfilter', function(Blueprint $table){

$table->integer('QueryFilterNum');

$table->string('GroupName');

$table->string('FilterText');



});

}


public function down()
{
Schema::dropIfExists('queryfilter');
}

};
