<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('orthocase', function(Blueprint $table){

$table->integer('OrthoCaseNum');

$table->integer('PatNum');

$table->integer('ProvNum');

$table->integer('ClinicNum');

$table->string('Fee');

$table->string('FeeInsPrimary');

$table->string('FeePat');

$table->date('BandingDate');

$table->date('DebondDate');

$table->date('DebondDateExpected');

$table->integer('IsTransfer');

$table->integer('OrthoType');

$table->date('SecDateTEntry');

$table->integer('SecUserNumEntry');

$table->string('SecDateTEdit');

$table->integer('IsActive');

$table->string('FeeInsSecondary');



});

}


public function down()
{
Schema::dropIfExists('orthocase');
}

};
