<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id(); // Primary key named 'id'
            $table->enum('vehicle_type', ['flight', 'bus', 'train']);
            $table->string('vehicle_name', 100);
            $table->integer('total_seats');
            $table->unsignedBigInteger('route_id')->nullable(); // Foreign key to routes

            $table->timestamps();

            // Add foreign key constraint on 'route_id' to reference 'id' in 'routes' table
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}