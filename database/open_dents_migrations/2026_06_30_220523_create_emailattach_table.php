<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('emailattach', function(Blueprint $table){

$table->integer('EmailAttachNum');

$table->integer('EmailMessageNum');

$table->string('DisplayedFileName');

$table->string('ActualFileName');

$table->integer('EmailTemplateNum');



});

}


public function down()
{
Schema::dropIfExists('emailattach');
}

};
