<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_applications')) {
            return;
        }

        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('visa_package_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('application_no', 50)->unique();
            $table->string('full_name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('nationality', 120)->nullable();
            $table->text('present_address')->nullable();
            $table->text('travel_purpose')->nullable();
            $table->date('travel_date')->nullable();
            $table->string('passport_no', 50);
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('country_name_snapshot', 150);
            $table->string('visa_type_snapshot', 50);
            $table->decimal('fee_snapshot', 10, 2);
            $table->string('currency_snapshot', 10)->default('BDT');
            $table->integer('processing_days_snapshot');
            $table->string('status', 50)->default('submitted');
            $table->string('payment_status', 50)->default('pending');
            $table->text('admin_note')->nullable();
            $table->text('approved_visa_file_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('rejection_letter_file_path')->nullable();
            $table->unsignedBigInteger('result_uploaded_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['user_id', 'status']);
            $table->index(['visa_package_id', 'status']);
            $table->index(['payment_status', 'created_at']);
            $table->index('passport_no');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('visa_package_id')
                ->references('id')
                ->on('visa_packages')
                ->onDelete('restrict');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('set null');

            $table->foreign('result_uploaded_by')
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
        Schema::dropIfExists('visa_applications');
    }
}
