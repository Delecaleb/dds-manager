<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailaddress', function (Blueprint $table) {

            $table->integer('EmailAddressNum');

            $table->string('SMTPserver');

            $table->string('EmailUsername');

            $table->string('EmailPassword');

            $table->integer('ServerPort');

            $table->integer('UseSSL');

            $table->string('SenderAddress');

            $table->string('Pop3ServerIncoming');

            $table->integer('ServerPortIncoming');

            $table->integer('UserNum');

            $table->string('AccessToken');

            $table->text('RefreshToken');

            $table->integer('DownloadInbox');

            $table->string('QueryString');

            $table->integer('AuthenticationType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailaddress');
    }
};
