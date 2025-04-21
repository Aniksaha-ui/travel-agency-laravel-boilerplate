<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuidePerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_performances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_id');
            $table->unsignedBigInteger('package_id');
            $table->decimal('rating', 3, 2)->nullable(); // Rating with 2 decimal places
            $table->text('feedback')->nullable(); // Feedback text
            $table->timestamps();
            $table->index('guide_id');
            $table->index('package_id');
            $table->foreign('guide_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('package_id')
                ->references('id')->on('packages')
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
        Schema::dropIfExists('guide_performances');
    }
}
