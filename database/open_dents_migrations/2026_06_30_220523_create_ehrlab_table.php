<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlab', function(Blueprint $table){

$table->integer('EhrLabNum');

$table->integer('PatNum');

$table->string('OrderControlCode');

$table->string('PlacerOrderNum');

$table->string('PlacerOrderNamespace');

$table->string('PlacerOrderUniversalID');

$table->string('PlacerOrderUniversalIDType');

$table->string('FillerOrderNum');

$table->string('FillerOrderNamespace');

$table->string('FillerOrderUniversalID');

$table->string('FillerOrderUniversalIDType');

$table->string('PlacerGroupNum');

$table->string('PlacerGroupNamespace');

$table->string('PlacerGroupUniversalID');

$table->string('PlacerGroupUniversalIDType');

$table->string('OrderingProviderID');

$table->string('OrderingProviderLName');

$table->string('OrderingProviderFName');

$table->string('OrderingProviderMiddleNames');

$table->string('OrderingProviderSuffix');

$table->string('OrderingProviderPrefix');

$table->string('OrderingProviderAssigningAuthorityNamespaceID');

$table->string('OrderingProviderAssigningAuthorityUniversalID');

$table->string('OrderingProviderAssigningAuthorityIDType');

$table->string('OrderingProviderNameTypeCode');

$table->string('OrderingProviderIdentifierTypeCode');

$table->integer('SetIdOBR');

$table->string('UsiID');

$table->string('UsiText');

$table->string('UsiCodeSystemName');

$table->string('UsiIDAlt');

$table->string('UsiTextAlt');

$table->string('UsiCodeSystemNameAlt');

$table->string('UsiTextOriginal');

$table->string('ObservationDateTimeStart');

$table->string('ObservationDateTimeEnd');

$table->string('SpecimenActionCode');

$table->string('ResultDateTime');

$table->string('ResultStatus');

$table->string('ParentObservationID');

$table->string('ParentObservationText');

$table->string('ParentObservationCodeSystemName');

$table->string('ParentObservationIDAlt');

$table->string('ParentObservationTextAlt');

$table->string('ParentObservationCodeSystemNameAlt');

$table->string('ParentObservationTextOriginal');

$table->string('ParentObservationSubID');

$table->string('ParentPlacerOrderNum');

$table->string('ParentPlacerOrderNamespace');

$table->string('ParentPlacerOrderUniversalID');

$table->string('ParentPlacerOrderUniversalIDType');

$table->string('ParentFillerOrderNum');

$table->string('ParentFillerOrderNamespace');

$table->string('ParentFillerOrderUniversalID');

$table->string('ParentFillerOrderUniversalIDType');

$table->integer('ListEhrLabResultsHandlingF');

$table->integer('ListEhrLabResultsHandlingN');

$table->integer('TQ1SetId');

$table->string('TQ1DateTimeStart');

$table->string('TQ1DateTimeEnd');

$table->integer('IsCpoe');

$table->text('OriginalPIDSegment');



});

}


public function down()
{
Schema::dropIfExists('ehrlab');
}

};
