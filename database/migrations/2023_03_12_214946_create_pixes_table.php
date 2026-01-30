<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pixes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('path');
            $table->string('replace_for_uuid')->index()->nullable();
            $table->string('my_number', 255)->index();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->string('key')->index();
            $table->string('pix_copy_and_paste')->index();
            $table->string('loc_id')->index();
            $table->string('location');
            $table->string('tipoCob');
            $table->string('create_date_pix', 50)->index();
            $table->integer('expiration_pix')->index();
            $table->date('due_date')->index();
            $table->string('status')->index();
            $table->decimal('original_price', 10, 2)->index();
            $table->decimal('amount_paid', 10, 2)->nullable()->index();
            $table->string('modalidadeAlteracao');
            $table->string('txid')->index();
            $table->integer('revision');
            $table->string('link_qrcode')->nullable();
            $table->text('services')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('datetime_situation', 255)->nullable();
            $table->date('payment_date')->index()->nullable();
            $table->date('cancellation_date')->index()->nullable();
            $table->timestamp('email_notify_send')->nullable();
            $table->timestamp('whatsapp_notify_send')->nullable();
            $table->timestamp('voicer_notify_send')->nullable();
            $table->timestamp('sms_notify_send')->nullable();
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
        Schema::dropIfExists('pixes');
    }
}