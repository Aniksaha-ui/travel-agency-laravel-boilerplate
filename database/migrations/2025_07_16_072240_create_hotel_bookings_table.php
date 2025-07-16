<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained('hotels')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('total_persons');
            $table->decimal('total_cost', 10, 2);
            $table->enum('booking_type', ['direct', 'package']);
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->enum('booking_status', ['confirmed', 'cancelled', 'checked_in', 'checked_out'])->default('confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel_bookings');
    }
}
