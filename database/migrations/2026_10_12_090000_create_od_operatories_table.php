<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OpenDental `operatory` — the chairs/rooms appointments are booked into. The Appointments
 * Calendar draws one column per operatory; without this table it had to guess columns and
 * dropped appointments whose operatory it did not know (e.g. a second clinic's chairs).
 *
 * OperatoryNum is unique across one OpenDental database, so a multi-clinic database numbers
 * each clinic's chairs separately; ClinicNum says which clinic a chair belongs to.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('od_operatories')) {
            return;
        }

        Schema::create('od_operatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id')->default(1)->index();
            $table->bigInteger('OperatoryNum');
            $table->string('OpName', 255)->nullable();
            $table->string('Abbrev', 255)->nullable();
            $table->integer('ItemOrder')->nullable()->default(0);
            $table->boolean('IsHidden')->default(false);
            $table->bigInteger('ProvDentist')->nullable();
            $table->bigInteger('ProvHygienist')->nullable();
            $table->boolean('IsHygiene')->default(false);
            $table->bigInteger('ClinicNum')->nullable()->default(0);
            $table->dateTime('DateTStamp')->nullable()->index();
            $table->boolean('SetProspective')->default(false);
            $table->boolean('IsWebSched')->default(false);
            $table->boolean('IsNewPatAppt')->default(false);
            $table->tinyInteger('OperatoryType')->nullable();
            $table->timestamps();

            $table->unique(['office_id', 'OperatoryNum'], 'od_operatories_office_opnum_unique');
            $table->index(['office_id', 'ClinicNum', 'IsHidden'], 'od_operatories_office_clinic_hidden_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('od_operatories');
    }
};
