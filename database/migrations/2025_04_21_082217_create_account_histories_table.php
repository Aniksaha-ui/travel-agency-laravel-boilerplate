<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('user_account_type', 20)->nullable();
            $table->string('user_account_no', 20)->nullable();
            $table->char('getaway', 20);
            $table->integer('amount');
            $table->string('com_account_no', 20)->nullable();
            $table->string('transaction_reference', 20);
            $table->enum('transaction_type', ['c', 'd']);
            $table->string('purpose', 20);
            $table->dateTime('tran_date')->nullable();
            $table->string('ip_address', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_histories');
    }
}
