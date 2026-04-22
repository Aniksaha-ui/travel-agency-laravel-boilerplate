<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationBellTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_notification_bell')) {
            return;
        }

        Schema::create('user_notification_bell', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title', 191);
            $table->text('content');
            $table->timestamp('schedule_start')->nullable();
            $table->timestamp('schedule_end')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('type', 50)->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->tinyInteger('is_read')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'user_notification_bell_user_status_idx');
            $table->index(['user_id', 'type', 'is_read'], 'user_notification_bell_read_idx');
            $table->index(['reference_type', 'reference_id'], 'user_notification_bell_reference_idx');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification_bell');
    }
}
