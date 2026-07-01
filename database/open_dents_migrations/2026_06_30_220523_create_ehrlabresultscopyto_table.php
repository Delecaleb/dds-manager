<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlabresultscopyto', function(Blueprint $table){

$table->integer('EhrLabResultsCopyToNum');

$table->integer('EhrLabNum');

$table->string('CopyToID');

$table->string('CopyToLName');

$table->string('CopyToFName');

$table->string('CopyToMiddleNames');

$table->string('CopyToSuffix');

$table->string('CopyToPrefix');

$table->string('CopyToAssigningAuthorityNamespaceID');

$table->string('CopyToAssigningAuthorityUniversalID');

$table->string('CopyToAssigningAuthorityIDType');

$table->string('CopyToNameTypeCode');

$table->string('CopyToIdentifierTypeCode');



});

}


public function down()
{
Schema::dropIfExists('ehrlabresultscopyto');
}

};
