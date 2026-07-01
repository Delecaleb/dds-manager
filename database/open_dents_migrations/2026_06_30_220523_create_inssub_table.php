<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('inssub', function(Blueprint $table){

$table->integer('InsSubNum');

$table->integer('PlanNum');

$table->integer('Subscriber');

$table->date('DateEffective');

$table->date('DateTerm');

$table->integer('ReleaseInfo');

$table->integer('AssignBen');

$table->string('SubscriberID');

$table->text('BenefitNotes');

$table->text('SubscNote');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->string('SecurityHash');



});

}


public function down()
{
Schema::dropIfExists('inssub');
}

};
