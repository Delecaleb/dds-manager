<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('repeatcharge', function(Blueprint $table){

$table->integer('RepeatChargeNum');

$table->integer('PatNum');

$table->string('ProcCode');

$table->string('ChargeAmt');

$table->date('DateStart');

$table->date('DateStop');

$table->text('Note');

$table->integer('CopyNoteToProc');

$table->integer('CreatesClaim');

$table->integer('IsEnabled');

$table->integer('UsePrepay');

$table->text('Npi');

$table->text('ErxAccountId');

$table->text('ProviderName');

$table->string('ChargeAmtAlt');

$table->string('UnearnedTypes');

$table->integer('Frequency');



});

}


public function down()
{
Schema::dropIfExists('repeatcharge');
}

};
