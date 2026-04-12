<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_packages')) {
            return;
        }

        Schema::create('visa_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_country_id');
            $table->string('title', 191);
            $table->string('visa_type', 50);
            $table->decimal('fee', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->integer('processing_days');
            $table->string('entry_type', 50)->nullable();
            $table->integer('stay_validity_days')->nullable();
            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->unique(['visa_country_id', 'title']);
            $table->index(['visa_country_id', 'visa_type']);
            $table->index(['is_active', 'processing_days']);

            $table->foreign('visa_country_id')
                ->references('id')
                ->on('visa_countries')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visa_packages');
    }
}
