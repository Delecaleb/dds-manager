<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('utm', function(Blueprint $table){

$table->integer('UtmNum');

$table->string('CampaignName');

$table->string('MediumInfo');

$table->string('SourceInfo');



});

}


public function down()
{
Schema::dropIfExists('utm');
}

};
