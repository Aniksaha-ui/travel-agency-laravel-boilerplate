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
            $table->integer('ratings')->nullable();
            $table->timestamps();
            $table->foreign('guide_id')->references('id')->on('guides')->onDelete('cascade');

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