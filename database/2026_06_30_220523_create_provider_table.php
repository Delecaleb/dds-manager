<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('provider', function(Blueprint $table){

$table->integer('ProvNum');

$table->string('Abbr');

$table->integer('ItemOrder');

$table->string('LName');

$table->string('FName');

$table->string('MI');

$table->string('Suffix');

$table->integer('FeeSched');

$table->integer('Specialty');

$table->string('SSN');

$table->string('StateLicense');

$table->string('DEANum');

$table->integer('IsSecondary');

$table->integer('ProvColor');

$table->integer('IsHidden');

$table->integer('UsingTIN');

$table->string('BlueCrossID');

$table->integer('SigOnFile');

$table->string('MedicaidID');

$table->integer('OutlineColor');

$table->integer('SchoolClassNum');

$table->string('NationalProvID');

$table->string('CanadianOfficeNum');

$table->string('DateTStamp');

$table->integer('AnesthProvType');

$table->string('TaxonomyCodeOverride');

$table->integer('IsCDAnet');

$table->string('EcwID');

$table->string('StateRxID');

$table->integer('IsNotPerson');

$table->string('StateWhereLicensed');

$table->integer('EmailAddressNum');

$table->integer('IsInstructor');

$table->integer('EhrMuStage');

$table->integer('ProvNumBillingOverride');

$table->string('CustomID');

$table->integer('ProvStatus');

$table->integer('IsHiddenReport');

$table->integer('IsErxEnabled');

$table->date('Birthdate');

$table->string('SchedNote');

$table->string('WebSchedDescript');

$table->string('WebSchedImageLocation');

$table->string('HourlyProdGoalAmt');

$table->date('DateTerm');

$table->string('PreferredName');



});

}


public function down()
{
Schema::dropIfExists('provider');
}

};
