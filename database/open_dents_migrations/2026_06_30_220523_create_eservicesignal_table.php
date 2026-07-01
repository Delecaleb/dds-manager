<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('eservicesignal', function(Blueprint $table){

$table->integer('EServiceSignalNum');

$table->integer('ServiceCode');

$table->integer('ReasonCategory');

$table->integer('ReasonCode');

$table->integer('Severity');

$table->text('Description');

$table->date('SigDateTime');

$table->text('Tag');

$table->integer('IsProcessed');



});

}


public function down()
{
Schema::dropIfExists('eservicesignal');
}

};
