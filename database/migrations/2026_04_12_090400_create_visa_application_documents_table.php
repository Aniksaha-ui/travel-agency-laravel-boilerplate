<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisaApplicationDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('visa_application_documents')) {
            return;
        }

        Schema::create('visa_application_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id');
            $table->unsignedBigInteger('visa_package_required_document_id')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('document_key', 80);
            $table->string('document_label', 120);
            $table->string('original_name', 255)->nullable();
            $table->text('file_path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('verification_status', 30)->default('pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['visa_application_id', 'verification_status'], 'visa_app_doc_verify_idx');
            $table->index(['visa_package_required_document_id'], 'visa_app_doc_required_idx');

            $table->foreign('visa_application_id')
                ->references('id')
                ->on('visa_applications')
                ->onDelete('cascade');

            $table->foreign('visa_package_required_document_id')
                ->references('id')
                ->on('visa_package_required_documents')
                ->onDelete('set null');

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visa_application_documents');
    }
}
