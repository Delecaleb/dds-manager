<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('apptreminderrule', function (Blueprint $table) {

            $table->integer('ApptReminderRuleNum');

            $table->integer('TypeCur');

            $table->integer('TSPrior');

            $table->string('SendOrder');

            $table->integer('IsSendAll');

            $table->text('TemplateSMS');

            $table->text('TemplateEmailSubject');

            $table->text('TemplateEmail');

            $table->integer('ClinicNum');

            $table->text('TemplateSMSAggShared');

            $table->text('TemplateSMSAggPerAppt');

            $table->text('TemplateEmailSubjAggShared');

            $table->text('TemplateEmailAggShared');

            $table->text('TemplateEmailAggPerAppt');

            $table->integer('DoNotSendWithin');

            $table->integer('IsEnabled');

            $table->text('TemplateAutoReply');

            $table->text('TemplateAutoReplyAgg');

            $table->integer('IsAutoReplyEnabled');

            $table->string('Language');

            $table->text('TemplateComeInMessage');

            $table->string('EmailTemplateType');

            $table->string('AggEmailTemplateType');

            $table->integer('IsSendForMinorsBirthday');

            $table->integer('EmailHostingTemplateNum');

            $table->integer('MinorAge');

            $table->text('TemplateFailureAutoReply');

            $table->integer('SendMultipleInvites');

            $table->integer('TimeSpanMultipleInvites');

        });

    }

    public function down()
    {
        Schema::dropIfExists('apptreminderrule');
    }
};
