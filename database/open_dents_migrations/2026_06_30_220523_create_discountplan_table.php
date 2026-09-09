<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('discountplan', function (Blueprint $table) {

            $table->integer('DiscountPlanNum');

            $table->string('Description');

            $table->integer('FeeSchedNum');

            $table->integer('DefNum');

            $table->integer('IsHidden');

            $table->text('PlanNote');

            $table->integer('ExamFreqLimit');

            $table->integer('XrayFreqLimit');

            $table->integer('ProphyFreqLimit');

            $table->integer('FluorideFreqLimit');

            $table->integer('PerioFreqLimit');

            $table->integer('LimitedExamFreqLimit');

            $table->integer('PAFreqLimit');

            $table->string('AnnualMax');

        });

    }

    public function down()
    {
        Schema::dropIfExists('discountplan');
    }
};
