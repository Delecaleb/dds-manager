<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('alertcategorylink', function (Blueprint $table) {

            $table->integer('AlertCategoryLinkNum');

            $table->integer('AlertCategoryNum');

            $table->integer('AlertType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('alertcategorylink');
    }
};
