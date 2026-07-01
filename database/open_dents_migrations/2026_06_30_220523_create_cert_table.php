<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('cert', function(Blueprint $table){

$table->integer('CertNum');

$table->string('Description');

$table->string('WikiPageLink');

$table->integer('ItemOrder');

$table->integer('IsHidden');

$table->integer('CertCategoryNum');



});

}


public function down()
{
Schema::dropIfExists('cert');
}

};
