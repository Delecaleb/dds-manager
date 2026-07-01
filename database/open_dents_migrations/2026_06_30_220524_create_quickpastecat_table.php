<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('quickpastecat', function(Blueprint $table){

$table->integer('QuickPasteCatNum');

$table->string('Description');

$table->integer('ItemOrder');

$table->text('DefaultForTypes');



});

}


public function down()
{
Schema::dropIfExists('quickpastecat');
}

};
