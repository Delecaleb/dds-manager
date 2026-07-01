<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('rxpat', function(Blueprint $table){

$table->integer('RxNum');

$table->integer('PatNum');

$table->date('RxDate');

$table->string('Drug');

$table->string('Sig');

$table->string('Disp');

$table->string('Refills');

$table->integer('ProvNum');

$table->string('Notes');

$table->integer('PharmacyNum');

$table->integer('IsControlled');

$table->string('DateTStamp');

$table->integer('SendStatus');

$table->integer('RxCui');

$table->string('DosageCode');

$table->string('ErxGuid');

$table->integer('IsErxOld');

$table->string('ErxPharmacyInfo');

$table->integer('IsProcRequired');

$table->integer('ProcNum');

$table->string('DaysOfSupply');

$table->text('PatientInstruction');

$table->integer('ClinicNum');

$table->integer('UserNum');

$table->integer('RxType');



});

}


public function down()
{
Schema::dropIfExists('rxpat');
}

};
