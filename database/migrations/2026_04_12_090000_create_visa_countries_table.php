<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_countries')) {
            return;
        }

        Schema::create('visa_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('iso_code', 10)->nullable()->unique();
            $table->string('nationality_name', 150)->nullable();
            $table->integer('display_order')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visa_countries');
    }
}
