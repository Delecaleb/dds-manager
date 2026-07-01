<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('tsitranslog', function(Blueprint $table){

$table->integer('TsiTransLogNum');

$table->integer('PatNum');

$table->integer('UserNum');

$table->integer('TransType');

$table->date('TransDateTime');

$table->integer('ServiceType');

$table->integer('ServiceCode');

$table->string('TransAmt');

$table->string('AccountBalance');

$table->integer('FKeyType');

$table->integer('FKey');

$table->string('RawMsgText');

$table->string('ClientId');

$table->text('TransJson');

$table->integer('ClinicNum');

$table->integer('AggTransLogNum');



});

}


public function down()
{
Schema::dropIfExists('tsitranslog');
}

};
