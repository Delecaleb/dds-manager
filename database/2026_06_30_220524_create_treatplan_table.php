<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('treatplan', function(Blueprint $table){

$table->integer('TreatPlanNum');

$table->integer('PatNum');

$table->date('DateTP');

$table->string('Heading');

$table->text('Note');

$table->text('Signature');

$table->integer('SigIsTopaz');

$table->integer('ResponsParty');

$table->integer('DocNum');

$table->integer('TPStatus');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->string('SecDateTEdit');

$table->integer('UserNumPresenter');

$table->integer('TPType');

$table->text('SignaturePractice');

$table->date('DateTSigned');

$table->date('DateTPracticeSigned');

$table->string('SignatureText');

$table->string('SignaturePracticeText');

$table->integer('MobileAppDeviceNum');



});

}


public function down()
{
Schema::dropIfExists('treatplan');
}

};
