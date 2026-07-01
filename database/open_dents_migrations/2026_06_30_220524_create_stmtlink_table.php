<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('stmtlink', function(Blueprint $table){

$table->integer('StmtLinkNum');

$table->integer('StatementNum');

$table->integer('StmtLinkType');

$table->integer('FKey');



});

}


public function down()
{
Schema::dropIfExists('stmtlink');
}

};
