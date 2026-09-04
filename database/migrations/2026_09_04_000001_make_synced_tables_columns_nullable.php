<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. od_pay_plan_charges
        if (Schema::hasTable('od_pay_plan_charges')) {
            Schema::table('od_pay_plan_charges', function (Blueprint $table) {
                if (Schema::hasColumn('od_pay_plan_charges', 'PayPlanNum')) {
                    $table->integer('PayPlanNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'Guarantor')) {
                    $table->integer('Guarantor')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'PatNum')) {
                    $table->integer('PatNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'ChargeDate')) {
                    $table->date('ChargeDate')->nullable()->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'Principal')) {
                    $table->string('Principal')->nullable()->default('0')->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'Interest')) {
                    $table->string('Interest')->nullable()->default('0')->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'Note')) {
                    $table->text('Note')->nullable()->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'ProvNum')) {
                    $table->integer('ProvNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'ClinicNum')) {
                    $table->integer('ClinicNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'ChargeType')) {
                    $table->integer('ChargeType')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'ProcNum')) {
                    $table->integer('ProcNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'SecDateTEntry')) {
                    $table->date('SecDateTEntry')->nullable()->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'SecDateTEdit')) {
                    $table->string('SecDateTEdit')->nullable()->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'StatementNum')) {
                    $table->integer('StatementNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'FKey')) {
                    $table->integer('FKey')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'LinkType')) {
                    $table->integer('LinkType')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'IsOffset')) {
                    $table->integer('IsOffset')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_pay_plan_charges', 'IsDownPayment')) {
                    $table->integer('IsDownPayment')->nullable()->default(0)->change();
                }
            });
        }

        // 2. od_claim_payments
        if (Schema::hasTable('od_claim_payments')) {
            Schema::table('od_claim_payments', function (Blueprint $table) {
                if (Schema::hasColumn('od_claim_payments', 'CheckDate')) {
                    $table->date('CheckDate')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'CheckAmt')) {
                    $table->string('CheckAmt')->nullable()->default('0')->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'CheckNum')) {
                    $table->string('CheckNum')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'BankBranch')) {
                    $table->string('BankBranch')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'Note')) {
                    $table->text('Note')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'ClinicNum')) {
                    $table->integer('ClinicNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'DepositNum')) {
                    $table->integer('DepositNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'CarrierName')) {
                    $table->string('CarrierName')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'DateIssued')) {
                    $table->date('DateIssued')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'IsPartial')) {
                    $table->integer('IsPartial')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'PayType')) {
                    $table->integer('PayType')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'SecUserNumEntry')) {
                    $table->integer('SecUserNumEntry')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'SecDateEntry')) {
                    $table->date('SecDateEntry')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'SecDateTEdit')) {
                    $table->string('SecDateTEdit')->nullable()->change();
                }
                if (Schema::hasColumn('od_claim_payments', 'PayGroup')) {
                    $table->integer('PayGroup')->nullable()->default(0)->change();
                }
            });
        }

        // 3. od_recalls
        if (Schema::hasTable('od_recalls')) {
            Schema::table('od_recalls', function (Blueprint $table) {
                if (Schema::hasColumn('od_recalls', 'PatNum')) {
                    $table->integer('PatNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'DateDueCalc')) {
                    $table->date('DateDueCalc')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'DateDue')) {
                    $table->date('DateDue')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'DatePrevious')) {
                    $table->date('DatePrevious')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'RecallInterval')) {
                    $table->integer('RecallInterval')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'RecallStatus')) {
                    $table->integer('RecallStatus')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'Note')) {
                    $table->text('Note')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'IsDisabled')) {
                    $table->integer('IsDisabled')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'DateTStamp')) {
                    $table->string('DateTStamp')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'RecallTypeNum')) {
                    $table->integer('RecallTypeNum')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'DisableUntilBalance')) {
                    $table->string('DisableUntilBalance')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'DisableUntilDate')) {
                    $table->date('DisableUntilDate')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'DateScheduled')) {
                    $table->date('DateScheduled')->nullable()->change();
                }
                if (Schema::hasColumn('od_recalls', 'Priority')) {
                    $table->integer('Priority')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recalls', 'TimePatternOverride')) {
                    $table->string('TimePatternOverride')->nullable()->change();
                }
            });
        }

        // 4. od_recall_types
        if (Schema::hasTable('od_recall_types')) {
            Schema::table('od_recall_types', function (Blueprint $table) {
                if (Schema::hasColumn('od_recall_types', 'Description')) {
                    $table->string('Description')->nullable()->change();
                }
                if (Schema::hasColumn('od_recall_types', 'DefaultInterval')) {
                    $table->integer('DefaultInterval')->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('od_recall_types', 'TimePattern')) {
                    $table->string('TimePattern')->nullable()->change();
                }
                if (Schema::hasColumn('od_recall_types', 'Procedures')) {
                    $table->string('Procedures')->nullable()->change();
                }
                if (Schema::hasColumn('od_recall_types', 'AppendToSpecial')) {
                    $table->integer('AppendToSpecial')->nullable()->default(0)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nullable columns do not need to be reverted to strict NOT NULL.
    }
};
