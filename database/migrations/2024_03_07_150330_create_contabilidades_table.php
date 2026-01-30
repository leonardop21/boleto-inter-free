<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContabilidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contabilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->index();
            $table->string('email')->index();
            $table->string('whatsapp')->index();
            $table->text('arquivo_pdf')->nullable();
            $table->enum('envio_email', ['YES', 'NO'])->default('NO');
            $table->enum('envio_whatsapp', ['YES', 'NO'])->default('NO');
            $table->timestamp('envio_email_em')->nullable();
            $table->timestamp('envio_whatsapp_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contabilidades');
    }
}
