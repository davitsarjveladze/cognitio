<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWallet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet', function (Blueprint $table) {
            $table->id('wallet_id');
            $table->text('name');
            $table->tinyText('currency_code');
            $table->unsignedBigInteger('user_id')->index();
            $table->bigInteger('balance')->default(0);
            $table->unsignedBigInteger('currency_id');
            $table->tinyInteger('status_id')->default(1)->comment('0-deleted, 1 - active,2-passive ');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('wallet');
    }
}
