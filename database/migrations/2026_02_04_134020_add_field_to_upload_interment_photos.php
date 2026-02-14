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
    Schema::create('upload_interment_photos', function (Blueprint $table) {
        $table->id();

        // Link to interment order / document
        $table->string('document_no')->index();

        // Who uploaded
        $table->string('uploader_name')->nullable();

        // Photo path / filename / URL
        $table->string('photo');

        // When family uploaded
        $table->timestamp('submitted_at')->nullable();

        // Validation flag (staff approval etc.)
        $table->boolean('is_valid')->default(true);

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_interment_photos');
    }
};
