<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsNfeCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('generate_nfe')->nullable()->after('status');
            $table->bigInteger('customer_nfe_type_id')->unsigned()->nullable()->after('generate_nfe');

            $table->foreign('customer_nfe_type_id')
            ->references('id')
            ->on('customer_nfe_types')->onUpdated('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}
