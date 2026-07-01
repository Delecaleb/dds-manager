<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimattach', function(Blueprint $table){

$table->integer('ClaimAttachNum');

$table->integer('ClaimNum');

$table->string('DisplayedFileName');

$table->string('ActualFileName');

$table->integer('ImageReferenceId');



});

}


public function down()
{
Schema::dropIfExists('claimattach');
}

};
