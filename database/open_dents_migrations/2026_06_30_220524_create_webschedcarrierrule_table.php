<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('webschedcarrierrule', function(Blueprint $table){

$table->integer('WebSchedCarrierRuleNum');

$table->integer('ClinicNum');

$table->string('CarrierName');

$table->string('DisplayName');

$table->text('Message');

$table->integer('Rule');



});

}


public function down()
{
Schema::dropIfExists('webschedcarrierrule');
}

};
