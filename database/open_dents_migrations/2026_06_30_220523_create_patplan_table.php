<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patplan', function(Blueprint $table){

$table->integer('PatPlanNum');

$table->integer('PatNum');

$table->integer('Ordinal');

$table->integer('IsPending');

$table->integer('Relationship');

$table->string('PatID');

$table->integer('InsSubNum');

$table->string('OrthoAutoFeeBilledOverride');

$table->date('OrthoAutoNextClaimDate');

$table->date('SecDateTEntry');

$table->string('SecDateTEdit');



});

}


public function down()
{
Schema::dropIfExists('patplan');
}

};
