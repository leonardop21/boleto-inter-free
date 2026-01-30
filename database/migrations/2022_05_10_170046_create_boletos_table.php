<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoletosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->string('codigo_solicitacao', 255)->index();
            $table->string('tipo_pagamento')->index()->nullable();
            $table->string('pix_txid', 255)->index()->nullable();
            $table->text('pixCopiaECola')->nullable();
            $table->decimal('price', 10, 2)->index();
            $table->decimal('price_paid', 10, 2)->index()->nullable()->comment('Valor pago pelo cliente');
            $table->text('description', 255)->nullable();
            $table->string('my_number', 255)->index()->nullable()->comment('Número utilizado para controle interno');
            $table->string('our_number', 255)->index()->nullable()->comment('Nosso Número, atribuído automaticamente pelo banco na emissão do título.');
            $table->string('bar_code', 255)->index()->nullable()->comment('Representação numérica do código de barras do título emitido.');
            $table->string('digitable_line')->index()->nullable()->comment('Contém os mesmos dados do bar code dispostos em ordem diferente e acrescido de dígitos verificadores');
            $table->string('path')->nullable()->comment('Path onde será salvo o boleto');
            $table->string('boleto')->nullable()->coment('Nome dado ao boleto no monento do download');
            $table->date('due_date')->index();
            $table->enum('status', ['PAGO', 'EM ABERTO', 'CANCELADO', 'VENCIDO', 'EXPIRADO'])->index();
            $table->date('payment_date')->index()->nullable();
            $table->date('cancellation_date')->index()->nullable();
            $table->integer('date_to_pay_after_due_date');
            $table->enum('code_multa', ['NAOTEMMULTA', 'VALORFIXO', 'PERCENTUAL'])->nullable();
            $table->string('data_multa', 10)->nullable();
            $table->string('taxa_multa', 10)->nullable();
            $table->string('valor_multa', 255)->nullable();
            $table->enum('code_mora', ['ISENTO', 'VALORDIA', 'TAXAMENSAL'])->nullable();
            $table->string('data_mora', 10)->nullable();
            $table->string('taxa_mora', 10)->nullable();
            $table->string('valor_mora', 255)->nullable();
            $table->timestamp('email_notify_send')->nullable();
            $table->timestamp('whatsapp_notify_send')->nullable();
            $table->timestamp('sms_notify_send')->nullable();
            $table->timestamp('voicer_notify_send')->nullable();
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
        Schema::dropIfExists('boletos');
    }
}
