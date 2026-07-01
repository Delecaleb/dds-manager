<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('substitutionlink', function(Blueprint $table){

$table->integer('SubstitutionLinkNum');

$table->integer('PlanNum');

$table->integer('CodeNum');

$table->string('SubstitutionCode');

$table->integer('SubstOnlyIf');



});

}


public function down()
{
Schema::dropIfExists('substitutionlink');
}

};
