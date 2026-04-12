<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaPackageRequiredDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_package_required_documents')) {
            return;
        }

        Schema::create('visa_package_required_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_package_id');
            $table->string('document_key', 80);
            $table->string('document_label', 120);
            $table->text('instructions')->nullable();
            $table->tinyInteger('is_required')->default(1);
            $table->tinyInteger('allow_multiple')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['visa_package_id', 'document_key'], 'visa_pkg_req_doc_unique');
            $table->index(['visa_package_id', 'sort_order'], 'visa_pkg_req_doc_sort_idx');

            $table->foreign('visa_package_id')
                ->references('id')
                ->on('visa_packages')
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
        Schema::dropIfExists('visa_package_required_documents');
    }
}
