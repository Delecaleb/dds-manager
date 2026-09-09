<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('equipment', function (Blueprint $table) {

            $table->integer('EquipmentNum');

            $table->text('Description');

            $table->string('SerialNumber');

            $table->string('ModelYear');

            $table->date('DatePurchased');

            $table->date('DateSold');

            $table->string('PurchaseCost');

            $table->string('MarketValue');

            $table->text('Location');

            $table->date('DateEntry');

            $table->integer('ProvNumCheckedOut');

            $table->date('DateCheckedOut');

            $table->date('DateExpectedBack');

            $table->text('DispenseNote');

            $table->text('Status');

        });

    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
};
