<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('hl7deffield', function(Blueprint $table){

$table->integer('HL7DefFieldNum');

$table->integer('HL7DefSegmentNum');

$table->integer('OrdinalPos');

$table->string('TableId');

$table->string('DataType');

$table->string('FieldName');

$table->text('FixedText');



});

}


public function down()
{
Schema::dropIfExists('hl7deffield');
}

};
