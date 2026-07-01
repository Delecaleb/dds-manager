<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('clearinghouse', function(Blueprint $table){

$table->integer('ClearinghouseNum');

$table->string('Description');

$table->text('ExportPath');

$table->text('Payors');

$table->integer('Eformat');

$table->string('ISA05');

$table->string('SenderTIN');

$table->string('ISA07');

$table->string('ISA08');

$table->string('ISA15');

$table->string('Password');

$table->string('ResponsePath');

$table->integer('CommBridge');

$table->string('ClientProgram');

$table->integer('LastBatchNumber');

$table->integer('ModemPort');

$table->string('LoginID');

$table->string('SenderName');

$table->string('SenderTelephone');

$table->string('GS03');

$table->string('ISA02');

$table->string('ISA04');

$table->string('ISA16');

$table->string('SeparatorData');

$table->string('SeparatorSegment');

$table->integer('ClinicNum');

$table->integer('HqClearinghouseNum');

$table->integer('IsEraDownloadAllowed');

$table->integer('IsClaimExportAllowed');

$table->integer('IsAttachmentSendAllowed');

$table->string('LocationID');

$table->integer('EnableXConnect');



});

}


public function down()
{
Schema::dropIfExists('clearinghouse');
}

};
