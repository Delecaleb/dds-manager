<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimvalcodelog', function(Blueprint $table){

$table->integer('ClaimValCodeLogNum');

$table->integer('ClaimNum');

$table->string('ClaimField');

$table->string('ValCode');

$table->string('ValAmount');

$table->integer('Ordinal');



});

}


public function down()
{
Schema::dropIfExists('claimvalcodelog');
}

};
