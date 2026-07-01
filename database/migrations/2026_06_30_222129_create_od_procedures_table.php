<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('od_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('CodeNum')->nullable();

            $table->string('ProcCode')->nullable();

            $table->string('Descript')->nullable();

            $table->string('AbbrDesc')->nullable();

            $table->string('ProcTime')->nullable();

            $table->string('ProcCat')->nullable();

            $table->string('TreatArea')->nullable();

            $table->string('NoBillIns')->nullable();

            $table->string('IsProsth')->nullable();

            $table->text('DefaultNote')->nullable();

            $table->string('IsHygiene')->nullable();

            $table->string('GTypeNum')->nullable();

            $table->string('AlternateCode1')->nullable();

            $table->string('MedicalCode')->nullable();

            $table->string('IsTaxed')->nullable();

            $table->string('PaintType')->nullable();

            $table->string('GraphicColor')->nullable();

            $table->string('LaymanTerm')->nullable();

            $table->string('IsCanadianLab')->nullable();

            $table->string('PreExisting')->nullable();

            $table->string('BaseUnits')->nullable();

            $table->string('SubstitutionCode')->nullable();

            $table->string('SubstOnlyIf')->nullable();

            $table->string('DateTStamp')->nullable();

            $table->string('IsMultiVisit')->nullable();

            $table->string('DrugNDC')->nullable();

            $table->string('RevenueCodeDefault')->nullable();

            $table->string('ProvNumDefault')->nullable();

            $table->string('CanadaTimeUnits')->nullable();

            $table->string('IsRadiology')->nullable();

            $table->text('DefaultClaimNote')->nullable();

            $table->text('DefaultTPNote')->nullable();

            $table->string('BypassGlobalLock')->nullable();

            $table->string('TaxCode')->nullable();

            $table->string('PaintText')->nullable();

            $table->string('AreaAlsoToothRange')->nullable();

            $table->string('DiagnosticCodes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_procedures');
    }
};
