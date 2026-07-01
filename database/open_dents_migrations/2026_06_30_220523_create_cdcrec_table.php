<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('cdcrec', function(Blueprint $table){

$table->integer('CdcrecNum');

$table->string('CdcrecCode');

$table->string('HeirarchicalCode');

$table->string('Description');



});

}


public function down()
{
Schema::dropIfExists('cdcrec');
}

};
