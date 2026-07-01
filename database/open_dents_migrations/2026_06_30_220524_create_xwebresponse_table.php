<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('xwebresponse', function(Blueprint $table){

$table->integer('XWebResponseNum');

$table->integer('PatNum');

$table->integer('ProvNum');

$table->integer('ClinicNum');

$table->integer('PaymentNum');

$table->date('DateTEntry');

$table->date('DateTUpdate');

$table->integer('TransactionStatus');

$table->integer('ResponseCode');

$table->string('XWebResponseCode');

$table->string('ResponseDescription');

$table->string('OTK');

$table->text('HpfUrl');

$table->date('HpfExpiration');

$table->string('TransactionID');

$table->string('TransactionType');

$table->string('Alias');

$table->string('CardType');

$table->string('CardBrand');

$table->string('CardBrandShort');

$table->string('MaskedAcctNum');

$table->string('Amount');

$table->string('ApprovalCode');

$table->string('CardCodeResponse');

$table->integer('ReceiptID');

$table->string('ExpDate');

$table->string('EntryMethod');

$table->string('ProcessorResponse');

$table->integer('BatchNum');

$table->string('BatchAmount');

$table->date('AccountExpirationDate');

$table->text('DebugError');

$table->text('PayNote');

$table->integer('CCSource');

$table->string('OrderId');

$table->string('EmailResponse');

$table->string('LogGuid');



});

}


public function down()
{
Schema::dropIfExists('xwebresponse');
}

};
