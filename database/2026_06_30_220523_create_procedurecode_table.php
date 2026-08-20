<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('procedurecode', function (Blueprint $table) {

            $table->integer('CodeNum');

            $table->string('ProcCode');

            $table->string('Descript');

            $table->string('AbbrDesc');

            $table->string('ProcTime');

            $table->integer('ProcCat');

            $table->integer('TreatArea');

            $table->integer('NoBillIns');

            $table->integer('IsProsth');

            $table->text('DefaultNote');

            $table->integer('IsHygiene');

            $table->integer('GTypeNum');

            $table->string('AlternateCode1');

            $table->string('MedicalCode');

            $table->integer('IsTaxed');

            $table->integer('PaintType');

            $table->integer('GraphicColor');

            $table->string('LaymanTerm');

            $table->integer('IsCanadianLab');

            $table->integer('PreExisting');

            $table->integer('BaseUnits');

            $table->string('SubstitutionCode');

            $table->integer('SubstOnlyIf');

            $table->string('DateTStamp');

            $table->integer('IsMultiVisit');

            $table->string('DrugNDC');

            $table->string('RevenueCodeDefault');

            $table->integer('ProvNumDefault');

            $table->string('CanadaTimeUnits');

            $table->integer('IsRadiology');

            $table->text('DefaultClaimNote');

            $table->text('DefaultTPNote');

            $table->integer('BypassGlobalLock');

            $table->string('TaxCode');

            $table->string('PaintText');

            $table->integer('AreaAlsoToothRange');

            $table->string('DiagnosticCodes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('procedurecode');
    }
};
