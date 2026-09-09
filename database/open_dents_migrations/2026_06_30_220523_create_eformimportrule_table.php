<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eformimportrule', function (Blueprint $table) {

            $table->integer('EFormImportRuleNum');

            $table->string('FieldName');

            $table->integer('Situation');

            $table->integer('Action');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eformimportrule');
    }
};
