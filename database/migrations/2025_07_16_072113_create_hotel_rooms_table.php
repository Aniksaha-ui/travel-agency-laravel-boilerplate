<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('room_types')->onUpdate('cascade')->onDelete('cascade');
            $table->string('room_size')->nullable();
            $table->integer('max_occupancy');
            $table->text('amenities')->nullable();
            $table->integer('total_rooms');
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
        Schema::dropIfExists('hotel_rooms');
    }
}
