<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('referral', function(Blueprint $table){

$table->integer('ReferralNum');

$table->string('LName');

$table->string('FName');

$table->string('MName');

$table->string('SSN');

$table->integer('UsingTIN');

$table->integer('Specialty');

$table->string('ST');

$table->string('Telephone');

$table->string('Address');

$table->string('Address2');

$table->string('City');

$table->string('Zip');

$table->text('Note');

$table->string('Phone2');

$table->integer('IsHidden');

$table->integer('NotPerson');

$table->string('Title');

$table->string('EMail');

$table->integer('PatNum');

$table->string('NationalProvID');

$table->integer('Slip');

$table->integer('IsDoctor');

$table->integer('IsTrustedDirect');

$table->string('DateTStamp');

$table->integer('IsPreferred');

$table->string('BusinessName');

$table->string('DisplayNote');



});

}


public function down()
{
Schema::dropIfExists('referral');
}

};
