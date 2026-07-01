<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('promotion', function(Blueprint $table){

$table->integer('PromotionNum');

$table->string('PromotionName');

$table->date('DateTimeCreated');

$table->integer('ClinicNum');

$table->integer('TypePromotion');



});

}


public function down()
{
Schema::dropIfExists('promotion');
}

};
