<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id');
            $table->string('trip_name');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->string('departure_at')->nullable();
            $table->string('arrival_at')->nullable();
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // Includes created_at and updated_at
            $table->string('status', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
