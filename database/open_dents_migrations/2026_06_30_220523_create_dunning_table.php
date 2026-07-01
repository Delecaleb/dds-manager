<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('dunning', function(Blueprint $table){

$table->integer('DunningNum');

$table->text('DunMessage');

$table->integer('BillingType');

$table->integer('AgeAccount');

$table->integer('InsIsPending');

$table->text('MessageBold');

$table->string('EmailSubject');

$table->text('EmailBody');

$table->integer('DaysInAdvance');

$table->integer('ClinicNum');

$table->integer('IsSuperFamily');

$table->integer('EmailType');



});

}


public function down()
{
Schema::dropIfExists('dunning');
}

};
