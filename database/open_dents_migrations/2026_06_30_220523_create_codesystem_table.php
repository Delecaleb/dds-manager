<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('codesystem', function(Blueprint $table){

$table->integer('CodeSystemNum');

$table->string('CodeSystemName');

$table->string('VersionCur');

$table->string('VersionAvail');

$table->string('HL7OID');

$table->string('Note');



});

}


public function down()
{
Schema::dropIfExists('codesystem');
}

};
