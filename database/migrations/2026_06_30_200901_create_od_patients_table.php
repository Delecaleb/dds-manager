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
        Schema::create('od_patients', function (Blueprint $table) {
            $table->id();
            $table->integer('PatNum')->unique();
            $table->string('FName')->nullable();
            $table->string('LName')->nullable();
            $table->string('Email')->nullable();
            $table->date('Birthdate')->nullable();
            $table->string('MiddleI')->nullable();
            $table->string("Preferred")->nullable();
            $table->string("PatStatus")->nullable();
            $table->string("Gender")->nullable();
            $table->string("Position")->nullable();
            $table->string('SSN')->nullable();
            $table->string('Address')->nullable();
            $table->string('Address2')->nullable();
            $table->string('City')->nullable();
            $table->string('State')->nullable();
            $table->string('Zip')->nullable();
            $table->string('HmPhone')->nullable();
            $table->string('WkPhone')->nullable();
            $table->string('WirelessPhone')->nullable();
            $table->string('Guarantor')->nullable();
            $table->string('CreditType')->nullable();
            $table->string('Salutation')->nullable();
            $table->string('EstBalance')->nullable();
            $table->string('PriProv')->nullable();
            $table->string('SecProv')->nullable();
            $table->string('FeeSched')->nullable();
            $table->string('BillingType')->nullable();
            $table->string('ImageFolder')->nullable();
            $table->string('AddrNote')->nullable();
            $table->string('FamFinUrgNote')->nullable();
            $table->string('MedUrgNote')->nullable();
            $table->string('ApptModNote')->nullable();
            $table->string('Fac')->nullable();
            $table->string('StudentStatus')->nullable();
            $table->string('SchoolName')->nullable();
            $table->string('ChartNumber')->nullable();
            $table->string('MedicaidID')->nullable();
            $table->string('Bal_0_30')->nullable();
            $table->string('Bal_31_60')->nullable();
            $table->string('Bal_61_90')->nullable();
            $table->string('BalOver90')->nullable();
            $table->string('InsEst')->nullable();
            $table->string('BalTotal')->nullable();
            $table->string('EmployerNum')->nullable();
            $table->string('EmploymentNote')->nullable();
            $table->string('County')->nullable();
            $table->string('GradeLevel')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_patients');
    }
};
