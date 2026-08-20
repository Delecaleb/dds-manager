<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('userod', function (Blueprint $table) {

            $table->integer('UserNum');

            $table->string('UserName');

            $table->string('Password');

            $table->integer('UserGroupNum');

            $table->integer('EmployeeNum');

            $table->integer('ClinicNum');

            $table->integer('ProvNum');

            $table->integer('IsHidden');

            $table->integer('TaskListInBox');

            $table->integer('AnesthProvType');

            $table->integer('DefaultHidePopups');

            $table->integer('PasswordIsStrong');

            $table->integer('ClinicIsRestricted');

            $table->integer('InboxHidePopups');

            $table->integer('UserNumCEMT');

            $table->date('DateTFail');

            $table->integer('FailedAttempts');

            $table->string('DomainUser');

            $table->integer('IsPasswordResetRequired');

            $table->string('MobileWebPin');

            $table->integer('MobileWebPinFailedAttempts');

            $table->date('DateTLastLogin');

            $table->string('EClipboardClinicalPin');

            $table->string('BadgeId');

        });

    }

    public function down()
    {
        Schema::dropIfExists('userod');
    }
};
