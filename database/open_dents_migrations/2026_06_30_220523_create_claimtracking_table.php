<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('claimtracking', function(Blueprint $table){

$table->integer('ClaimTrackingNum');

$table->integer('ClaimNum');

$table->string('TrackingType');

$table->integer('UserNum');

$table->string('DateTimeEntry');

$table->text('Note');

$table->integer('TrackingDefNum');

$table->integer('TrackingErrorDefNum');



});

}


public function down()
{
Schema::dropIfExists('claimtracking');
}

};
