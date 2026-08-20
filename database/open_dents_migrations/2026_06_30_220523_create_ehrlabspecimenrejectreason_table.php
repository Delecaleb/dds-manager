<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrlabspecimenrejectreason', function (Blueprint $table) {

            $table->integer('EhrLabSpecimenRejectReasonNum');

            $table->integer('EhrLabSpecimenNum');

            $table->string('SpecimenRejectReasonID');

            $table->string('SpecimenRejectReasonText');

            $table->string('SpecimenRejectReasonCodeSystemName');

            $table->string('SpecimenRejectReasonIDAlt');

            $table->string('SpecimenRejectReasonTextAlt');

            $table->string('SpecimenRejectReasonCodeSystemNameAlt');

            $table->string('SpecimenRejectReasonTextOriginal');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrlabspecimenrejectreason');
    }
};
