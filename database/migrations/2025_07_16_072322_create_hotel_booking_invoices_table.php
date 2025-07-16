<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelBookingInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_booking_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings')->onUpdate('cascade')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('issued_date');
            $table->decimal('total_amount', 10, 2);
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
        Schema::dropIfExists('hotel_booking_invoices');
    }
}
