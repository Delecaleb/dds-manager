<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehramendment', function(Blueprint $table){

$table->integer('EhrAmendmentNum');

$table->integer('PatNum');

$table->integer('IsAccepted');

$table->text('Description');

$table->integer('Source');

$table->text('SourceName');

$table->string('FileName');

$table->text('RawBase64');

$table->date('DateTRequest');

$table->date('DateTAcceptDeny');

$table->date('DateTAppend');



});

}


public function down()
{
Schema::dropIfExists('ehramendment');
}

};
