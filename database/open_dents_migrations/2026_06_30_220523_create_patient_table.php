<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patient', function(Blueprint $table){

$table->integer('PatNum');

$table->string('LName');

$table->string('FName');

$table->string('MiddleI');

$table->string('Preferred');

$table->integer('PatStatus');

$table->integer('Gender');

$table->integer('Position');

$table->date('Birthdate');

$table->string('SSN');

$table->string('Address');

$table->string('Address2');

$table->string('City');

$table->string('State');

$table->string('Zip');

$table->string('HmPhone');

$table->string('WkPhone');

$table->string('WirelessPhone');

$table->integer('Guarantor');

$table->string('CreditType');

$table->string('Email');

$table->string('Salutation');

$table->string('EstBalance');

$table->integer('PriProv');

$table->integer('SecProv');

$table->integer('FeeSched');

$table->integer('BillingType');

$table->string('ImageFolder');

$table->text('AddrNote');

$table->text('FamFinUrgNote');

$table->string('MedUrgNote');

$table->string('ApptModNote');

$table->string('StudentStatus');

$table->string('SchoolName');

$table->string('ChartNumber');

$table->string('MedicaidID');

$table->string('Bal_0_30');

$table->string('Bal_31_60');

$table->string('Bal_61_90');

$table->string('BalOver90');

$table->string('InsEst');

$table->string('BalTotal');

$table->integer('EmployerNum');

$table->string('EmploymentNote');

$table->string('County');

$table->integer('GradeLevel');

$table->integer('Urgency');

$table->date('DateFirstVisit');

$table->integer('ClinicNum');

$table->string('HasIns');

$table->string('TrophyFolder');

$table->integer('PlannedIsDone');

$table->integer('Premed');

$table->string('Ward');

$table->integer('PreferConfirmMethod');

$table->integer('PreferContactMethod');

$table->integer('PreferRecallMethod');

$table->string('SchedBeforeTime');

$table->string('SchedAfterTime');

$table->integer('SchedDayOfWeek');

$table->string('Language');

$table->date('AdmitDate');

$table->string('Title');

$table->string('PayPlanDue');

$table->integer('SiteNum');

$table->string('DateTStamp');

$table->integer('ResponsParty');

$table->integer('CanadianEligibilityCode');

$table->integer('AskToArriveEarly');

$table->integer('PreferContactConfidential');

$table->integer('SuperFamily');

$table->integer('TxtMsgOk');

$table->string('SmokingSnoMed');

$table->string('Country');

$table->date('DateTimeDeceased');

$table->integer('BillingCycleDay');

$table->integer('SecUserNumEntry');

$table->date('SecDateEntry');

$table->integer('HasSuperBilling');

$table->integer('PatNumCloneFrom');

$table->integer('DiscountPlanNum');

$table->integer('HasSignedTil');

$table->integer('ShortCodeOptIn');

$table->string('SecurityHash');



});

}


public function down()
{
Schema::dropIfExists('patient');
}

};
