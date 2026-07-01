<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('custrefentry', function(Blueprint $table){

$table->integer('CustRefEntryNum');

$table->integer('PatNumCust');

$table->integer('PatNumRef');

$table->date('DateEntry');

$table->string('Note');



});

}


public function down()
{
Schema::dropIfExists('custrefentry');
}

};
