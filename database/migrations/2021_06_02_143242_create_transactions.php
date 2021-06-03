<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transactions_id');
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('receiver_id')->index();
            $table->unsignedBigInteger('sender_wallet_id')->nullable();
            $table->unsignedBigInteger('receiver_wallet_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->bigInteger('value');
            $table->tinyText('currency_code')->nullable();
            $table->tinyInteger('type_id')->comment('1 - diff users transactions. 2 - exchange');
            $table->tinyInteger('status_id')->default(1)->comment('0- deleted, pending -1, reject-2, confirmed - 3');
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->foreign('sender_wallet_id')->references('wallet_id')->on('wallet');
            $table->foreign('receiver_wallet_id')->references('wallet_id')->on('wallet');
            $table->foreign('currency_id')->references('currency_id')->on('currency_list');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
