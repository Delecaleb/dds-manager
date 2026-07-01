<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('securityloghash', function(Blueprint $table){

$table->integer('SecurityLogHashNum');

$table->integer('SecurityLogNum');

$table->string('LogHash');



});

}


public function down()
{
Schema::dropIfExists('securityloghash');
}

};
