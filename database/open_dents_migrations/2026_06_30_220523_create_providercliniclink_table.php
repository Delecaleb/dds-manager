<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('providercliniclink', function(Blueprint $table){

$table->integer('ProviderClinicLinkNum');

$table->integer('ProvNum');

$table->integer('ClinicNum');



});

}


public function down()
{
Schema::dropIfExists('providercliniclink');
}

};
