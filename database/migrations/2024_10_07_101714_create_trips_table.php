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
            $table->longText('description')->charset('utf8mb4')->collation('utf8mb4_general_ci');
            $table->dateTime('departure_time');
            $table->string('image')->charset('utf8mb4')->collation('utf8mb4_general_ci');
            $table->dateTime('arrival_time');
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->string('status')->charset('utf8mb4')->collation('utf8mb4_general_ci');

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');

            $table->index('vehicle_id');
            $table->index('route_id');
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