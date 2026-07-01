<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('ehrlabresult', function(Blueprint $table){

$table->integer('EhrLabResultNum');

$table->integer('EhrLabNum');

$table->integer('SetIdOBX');

$table->string('ValueType');

$table->string('ObservationIdentifierID');

$table->string('ObservationIdentifierText');

$table->string('ObservationIdentifierCodeSystemName');

$table->string('ObservationIdentifierIDAlt');

$table->string('ObservationIdentifierTextAlt');

$table->string('ObservationIdentifierCodeSystemNameAlt');

$table->string('ObservationIdentifierTextOriginal');

$table->string('ObservationIdentifierSub');

$table->string('ObservationValueCodedElementID');

$table->string('ObservationValueCodedElementText');

$table->string('ObservationValueCodedElementCodeSystemName');

$table->string('ObservationValueCodedElementIDAlt');

$table->string('ObservationValueCodedElementTextAlt');

$table->string('ObservationValueCodedElementCodeSystemNameAlt');

$table->string('ObservationValueCodedElementTextOriginal');

$table->string('ObservationValueDateTime');

$table->string('ObservationValueTime');

$table->string('ObservationValueComparator');

$table->string('ObservationValueNumber1');

$table->string('ObservationValueSeparatorOrSuffix');

$table->string('ObservationValueNumber2');

$table->string('ObservationValueNumeric');

$table->string('ObservationValueText');

$table->string('UnitsID');

$table->string('UnitsText');

$table->string('UnitsCodeSystemName');

$table->string('UnitsIDAlt');

$table->string('UnitsTextAlt');

$table->string('UnitsCodeSystemNameAlt');

$table->string('UnitsTextOriginal');

$table->string('referenceRange');

$table->string('AbnormalFlags');

$table->string('ObservationResultStatus');

$table->string('ObservationDateTime');

$table->string('AnalysisDateTime');

$table->string('PerformingOrganizationName');

$table->string('PerformingOrganizationNameAssigningAuthorityNamespaceId');

$table->string('PerformingOrganizationNameAssigningAuthorityUniversalId');

$table->string('PerformingOrganizationNameAssigningAuthorityUniversalIdType');

$table->string('PerformingOrganizationIdentifierTypeCode');

$table->string('PerformingOrganizationIdentifier');

$table->string('PerformingOrganizationAddressStreet');

$table->string('PerformingOrganizationAddressOtherDesignation');

$table->string('PerformingOrganizationAddressCity');

$table->string('PerformingOrganizationAddressStateOrProvince');

$table->string('PerformingOrganizationAddressZipOrPostalCode');

$table->string('PerformingOrganizationAddressCountryCode');

$table->string('PerformingOrganizationAddressAddressType');

$table->string('PerformingOrganizationAddressCountyOrParishCode');

$table->string('MedicalDirectorID');

$table->string('MedicalDirectorLName');

$table->string('MedicalDirectorFName');

$table->string('MedicalDirectorMiddleNames');

$table->string('MedicalDirectorSuffix');

$table->string('MedicalDirectorPrefix');

$table->string('MedicalDirectorAssigningAuthorityNamespaceID');

$table->string('MedicalDirectorAssigningAuthorityUniversalID');

$table->string('MedicalDirectorAssigningAuthorityIDType');

$table->string('MedicalDirectorNameTypeCode');

$table->string('MedicalDirectorIdentifierTypeCode');



});

}


public function down()
{
Schema::dropIfExists('ehrlabresult');
}

};
