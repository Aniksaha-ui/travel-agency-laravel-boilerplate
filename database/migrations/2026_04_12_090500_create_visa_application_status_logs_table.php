<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaApplicationStatusLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_application_status_logs')) {
            return;
        }

        Schema::create('visa_application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['visa_application_id', 'id'], 'visa_app_status_logs_app_idx');

            $table->foreign('visa_application_id')
                ->references('id')
                ->on('visa_applications')
                ->onDelete('cascade');

            $table->foreign('changed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visa_application_status_logs');
    }
}
