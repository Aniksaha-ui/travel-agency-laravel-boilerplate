<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id(); // Primary key named 'id'
            $table->unsignedBigInteger('vehicle_id'); // Foreign key to vehicles
            $table->string('seat_number', 10);
            $table->enum('seat_class', ['economy', 'business', 'first_class']);
            $table->enum('seat_type', ['window', 'aisle', 'middle']);

            $table->timestamps();

            // Add foreign key constraint on 'vehicle_id' to reference 'id' in 'vehicles' table
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
        Schema::dropIfExists('seats');
    }
}
