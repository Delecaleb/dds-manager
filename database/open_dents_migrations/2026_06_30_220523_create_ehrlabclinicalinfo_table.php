<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlabclinicalinfo', function(Blueprint $table){

$table->integer('EhrLabClinicalInfoNum');

$table->integer('EhrLabNum');

$table->string('ClinicalInfoID');

$table->string('ClinicalInfoText');

$table->string('ClinicalInfoCodeSystemName');

$table->string('ClinicalInfoIDAlt');

$table->string('ClinicalInfoTextAlt');

$table->string('ClinicalInfoCodeSystemNameAlt');

$table->string('ClinicalInfoTextOriginal');



});

}


public function down()
{
Schema::dropIfExists('ehrlabclinicalinfo');
}

};
