<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('document', function (Blueprint $table) {

            $table->integer('DocNum');

            $table->string('Description');

            $table->date('DateCreated');

            $table->integer('DocCategory');

            $table->integer('PatNum');

            $table->string('FileName');

            $table->integer('ImgType');

            $table->integer('IsFlipped');

            $table->string('DegreesRotated');

            $table->string('ToothNumbers');

            $table->text('Note');

            $table->integer('SigIsTopaz');

            $table->text('Signature');

            $table->integer('CropX');

            $table->integer('CropY');

            $table->integer('CropW');

            $table->integer('CropH');

            $table->integer('WindowingMin');

            $table->integer('WindowingMax');

            $table->integer('MountItemNum');

            $table->string('DateTStamp');

            $table->text('RawBase64');

            $table->text('Thumbnail');

            $table->string('ExternalGUID');

            $table->string('ExternalSource');

            $table->integer('ProvNum');

            $table->integer('IsCropOld');

            $table->text('OcrResponseData');

            $table->integer('ImageCaptureType');

            $table->integer('PrintHeading');

            $table->integer('ChartLetterStatus');

            $table->integer('UserNum');

            $table->string('ChartLetterHash');

        });

    }

    public function down()
    {
        Schema::dropIfExists('document');
    }
};
