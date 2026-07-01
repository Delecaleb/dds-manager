<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('medlabresult', function(Blueprint $table){

$table->integer('MedLabResultNum');

$table->integer('MedLabNum');

$table->string('ObsID');

$table->string('ObsText');

$table->string('ObsLoinc');

$table->string('ObsLoincText');

$table->string('ObsIDSub');

$table->text('ObsValue');

$table->string('ObsSubType');

$table->string('ObsUnits');

$table->string('ReferenceRange');

$table->string('AbnormalFlag');

$table->string('ResultStatus');

$table->date('DateTimeObs');

$table->string('FacilityID');

$table->integer('DocNum');

$table->text('Note');



});

}


public function down()
{
Schema::dropIfExists('medlabresult');
}

};
