<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('registrationkey', function (Blueprint $table) {

            $table->integer('RegistrationKeyNum');

            $table->integer('PatNum');

            $table->string('RegKey');

            $table->string('Note');

            $table->date('DateStarted');

            $table->date('DateDisabled');

            $table->date('DateEnded');

            $table->integer('IsForeign');

            $table->integer('UsesServerVersion');

            $table->integer('IsFreeVersion');

            $table->integer('IsOnlyForTesting');

            $table->integer('VotesAllotted');

            $table->integer('IsResellerCustomer');

            $table->integer('HasEarlyAccess');

            $table->date('DateTBackupScheduled');

            $table->string('BackupPassCode');

            $table->date('DateTClinicAccess');

        });

    }

    public function down()
    {
        Schema::dropIfExists('registrationkey');
    }
};
