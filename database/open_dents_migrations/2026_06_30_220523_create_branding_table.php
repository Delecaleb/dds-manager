<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('branding', function (Blueprint $table) {

            $table->integer('BrandingNum');

            $table->integer('BrandingType');

            $table->integer('ClinicNum');

            $table->text('ValueString');

            $table->date('DateTimeUpdated');

        });

    }

    public function down()
    {
        Schema::dropIfExists('branding');
    }
};
