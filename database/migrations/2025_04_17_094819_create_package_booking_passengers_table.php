<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageBookingPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_booking_id');
            $table->enum('type', ['adult', 'child']);
            $table->timestamps();
            $table->foreign('package_booking_id')
                ->references('id')
                ->on('package_bookings')
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
        Schema::dropIfExists('package_booking_passengers');
    }
}
