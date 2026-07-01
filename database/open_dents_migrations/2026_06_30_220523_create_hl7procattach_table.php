<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('hl7procattach', function(Blueprint $table){

$table->integer('HL7ProcAttachNum');

$table->integer('HL7MsgNum');

$table->integer('ProcNum');



});

}


public function down()
{
Schema::dropIfExists('hl7procattach');
}

};
