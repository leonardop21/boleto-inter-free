<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('corporate_name', 255)->nullable()->index();
            $table->string('razao_social', 255)->nullable()->index();
            $table->string('fantasy_name', 255)->nullable()->index();
            $table->string('cpf_cnpj', 50)->index();
            $table->enum('type_of_person', ['fisica', 'juridica']);
            $table->string('zipcode', 20);
            $table->string('address', 255);
            $table->string('address_number', 10);
            $table->string('city', 30)->index();
            $table->string('state', 2)->index();
            $table->string('district', 255)->nullable();
            $table->string('complement', 255)->nullable();
            $table->string('email', 255)->index();
            $table->string('phone', 20)->index()->nullable();
            $table->string('whatsapp', 20)->index()->nullable();
            $table->string('telegram', 50)->nullable();
            $table->integer('expiration_day_boleto')->comment('Dia do vencimento do boleto');
            $table->enum('code_multa', ['NAOTEMMULTA', 'VALORFIXO', 'PERCENTUAL'])->nullable();
            $table->string('data_multa')->nullable();
            $table->string('taxa_multa')->nullable();
            $table->string('valor_multa')->nullable();
            $table->enum('code_mora', ['ISENTO', 'VALORDIA', 'TAXAMENSAL'])->nullable();
            $table->string('data_mora')->nullable();
            $table->string('taxa_mora')->nullable();
            $table->string('valor_mora')->nullable();
            $table->integer('date_to_pay_after_due_date')->nullable();
            $table->enum('email_notify', [0,1]);
            $table->enum('whatsapp_notify', [0,1]);
            $table->enum('telegram_notify', [0,1]);
            $table->enum('status', [0,1]);
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
        Schema::dropIfExists('customers');
    }
}
