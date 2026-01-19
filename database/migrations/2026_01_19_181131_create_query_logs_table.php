<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('query_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id');
            $table->text('sql');
            $table->longText('bindings')->nullable();
            $table->decimal('time_ms', 8, 2);
            $table->string('connection', 50);
            $table->string('url');
            $table->string('method', 10);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_slow')->default(false);
            $table->timestamps();

            $table->index(['request_id']);
            $table->index(['user_id']);
            $table->index(['is_slow']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('query_logs');
    }
}
