<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wbs_i_upload_interment_slideshow_photos', function (Blueprint $table) {
            $table->id();

            // Link to interment order / document
            $table->string('document_no')->index();

            // Photo path / filename / URL
            $table->string('photo');

            // When family uploaded
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // Optional: foreign key
            // $table->foreign('document_no')->references('document_no')->on('wbs_i_upload_interment_photos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_interment_slideshow_photos');
    }
};
