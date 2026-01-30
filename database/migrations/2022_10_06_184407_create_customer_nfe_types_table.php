<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerNfeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_nfe_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_item_service')->comment('codigo_item_lista_servico');
            $table->string('nbs_code')->comment('codigo_nbs');
            $table->string('nbs_description')->comment('descricao_nbs');
            $table->string('aliquota_item_service')->comment('aliquota_item_lista_servico');
            $table->string('deduction_value')->comment('valor_deducao')->nullable();
            $table->string('issr_value')->comment('valor_deducao')->nullable();
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
        Schema::dropIfExists('customer_nfe_types');
    }
}
