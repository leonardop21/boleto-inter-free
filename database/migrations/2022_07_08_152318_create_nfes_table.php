<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNfesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->string("nfe_number")->index();
            $table->bigInteger('boleto_id')->unsigned()->nullable();
            $table->string("serie_nfse");
            $table->string("date_time")->index();
            $table->string("situation_code_nfe", 10)->index()->comment('situacao_codigo_nfse');
            $table->string("situation_description_nfe", 255)->index()->comment('situacao_descricao_nfse');
            $table->text("link_nfse");
            $table->string("authenticity_verifier_code")->index()->comment('Código verificador de autenticidade');
            $table->timestamp('email_notify_send')->nullable();
            $table->timestamp('whatsapp_notify_send')->nullable();
            $table->timestamp('telegram_notify_send')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
            ->references('id')
            ->on('customers')->onUpdated('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nfes');
    }
}
