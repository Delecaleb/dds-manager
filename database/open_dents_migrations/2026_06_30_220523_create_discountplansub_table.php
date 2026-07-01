<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('discountplansub', function(Blueprint $table){

$table->integer('DiscountSubNum');

$table->integer('DiscountPlanNum');

$table->integer('PatNum');

$table->date('DateEffective');

$table->date('DateTerm');

$table->text('SubNote');



});

}


public function down()
{
Schema::dropIfExists('discountplansub');
}

};
