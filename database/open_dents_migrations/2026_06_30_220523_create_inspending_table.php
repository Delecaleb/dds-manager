<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('inspending', function(Blueprint $table){

$table->integer('InsPendingNum');

$table->integer('PatNum');

$table->integer('PatNumSubscriber');

$table->integer('Ordinal');

$table->integer('Relationship');

$table->string('GroupNum');

$table->string('GroupName');

$table->string('Employer');

$table->string('SubscriberID');

$table->string('Phone');

$table->string('CarrierName');



});

}


public function down()
{
Schema::dropIfExists('inspending');
}

};
