<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('alertcategory', function(Blueprint $table){

$table->integer('AlertCategoryNum');

$table->integer('IsHQCategory');

$table->string('InternalName');

$table->string('Description');



});

}


public function down()
{
Schema::dropIfExists('alertcategory');
}

};
