<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('treatplanattach', function(Blueprint $table){

$table->integer('TreatPlanAttachNum');

$table->integer('TreatPlanNum');

$table->integer('ProcNum');

$table->integer('Priority');



});

}


public function down()
{
Schema::dropIfExists('treatplanattach');
}

};
