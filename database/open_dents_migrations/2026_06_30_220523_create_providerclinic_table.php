<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('providerclinic', function(Blueprint $table){

$table->integer('ProviderClinicNum');

$table->integer('ProvNum');

$table->integer('ClinicNum');

$table->string('DEANum');

$table->string('StateLicense');

$table->string('StateRxID');

$table->string('StateWhereLicensed');

$table->string('CareCreditMerchantId');



});

}


public function down()
{
Schema::dropIfExists('providerclinic');
}

};
