<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('hl7def', function(Blueprint $table){

$table->integer('HL7DefNum');

$table->string('Description');

$table->integer('ModeTx');

$table->string('IncomingFolder');

$table->string('OutgoingFolder');

$table->string('IncomingPort');

$table->string('OutgoingIpPort');

$table->string('FieldSeparator');

$table->string('ComponentSeparator');

$table->string('SubcomponentSeparator');

$table->string('RepetitionSeparator');

$table->string('EscapeCharacter');

$table->integer('IsInternal');

$table->string('InternalType');

$table->string('InternalTypeVersion');

$table->integer('IsEnabled');

$table->text('Note');

$table->string('HL7Server');

$table->string('HL7ServiceName');

$table->integer('ShowDemographics');

$table->integer('ShowAppts');

$table->integer('ShowAccount');

$table->integer('IsQuadAsToothNum');

$table->integer('LabResultImageCat');

$table->string('SftpUsername');

$table->string('SftpPassword');

$table->string('SftpInSocket');

$table->integer('HasLongDCodes');

$table->integer('IsProcApptEnforced');



});

}


public function down()
{
Schema::dropIfExists('hl7def');
}

};
