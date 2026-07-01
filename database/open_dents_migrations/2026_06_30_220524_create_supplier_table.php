<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('supplier', function(Blueprint $table){

$table->integer('SupplierNum');

$table->string('Name');

$table->string('Phone');

$table->string('CustomerId');

$table->text('Website');

$table->string('UserName');

$table->string('Password');

$table->text('Note');



});

}


public function down()
{
Schema::dropIfExists('supplier');
}

};
