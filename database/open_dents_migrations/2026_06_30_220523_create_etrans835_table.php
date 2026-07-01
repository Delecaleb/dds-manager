<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('etrans835', function(Blueprint $table){

$table->integer('Etrans835Num');

$table->integer('EtransNum');

$table->string('PayerName');

$table->string('TransRefNum');

$table->string('InsPaid');

$table->string('ControlId');

$table->string('PaymentMethodCode');

$table->string('PatientName');

$table->integer('Status');

$table->integer('AutoProcessed');

$table->integer('IsApproved');



});

}


public function down()
{
Schema::dropIfExists('etrans835');
}

};
