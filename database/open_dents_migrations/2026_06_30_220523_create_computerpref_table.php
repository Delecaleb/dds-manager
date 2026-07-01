<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('computerpref', function(Blueprint $table){

$table->integer('ComputerPrefNum');

$table->string('ComputerName');

$table->integer('GraphicsUseHardware');

$table->integer('GraphicsSimple');

$table->string('SensorType');

$table->integer('SensorBinned');

$table->integer('SensorPort');

$table->integer('SensorExposure');

$table->integer('GraphicsDoubleBuffering');

$table->integer('PreferredPixelFormatNum');

$table->string('AtoZpath');

$table->integer('TaskKeepListHidden');

$table->integer('TaskDock');

$table->integer('TaskX');

$table->integer('TaskY');

$table->string('DirectXFormat');

$table->integer('ScanDocSelectSource');

$table->integer('ScanDocShowOptions');

$table->integer('ScanDocDuplex');

$table->integer('ScanDocGrayscale');

$table->integer('ScanDocResolution');

$table->integer('ScanDocQuality');

$table->integer('ClinicNum');

$table->integer('ApptViewNum');

$table->integer('RecentApptView');

$table->integer('PatSelectSearchMode');

$table->integer('NoShowLanguage');

$table->integer('NoShowDecimal');

$table->string('ComputerOS');

$table->string('HelpButtonXAdjustment');

$table->integer('GraphicsUseDirectX11');

$table->integer('Zoom');

$table->string('VideoRectangle');

$table->string('CreditCardTerminalId');



});

}


public function down()
{
Schema::dropIfExists('computerpref');
}

};
